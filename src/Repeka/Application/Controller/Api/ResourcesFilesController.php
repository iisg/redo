<?php
namespace Repeka\Application\Controller\Api;

use Assert\Assertion;
use elFinder;
use elFinderConnector;
use Repeka\Application\Serialization\ResourceNormalizer;
use Repeka\Domain\Constants\SystemRole;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Service\ResourceDisplayStrategyEvaluator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/resources")
 */
class ResourcesFilesController extends ApiController {
    /** @var ResourceDisplayStrategyEvaluator */
    private $displayStrategyEvaluator;
    /** @var MetadataRepository */
    private $metadataRepository;
    /** @var ResourceNormalizer */
    private $resourceNormalizer;

    public function __construct(
        ResourceDisplayStrategyEvaluator $displayStrategyEvaluator,
        MetadataRepository $metadataRepository,
        ResourceNormalizer $resourceNormalizer
    ) {
        $this->displayStrategyEvaluator = $displayStrategyEvaluator;
        $this->metadataRepository = $metadataRepository;
        $this->resourceNormalizer = $resourceNormalizer;
    }

    /**
     * @Route("/{resource}/file-manager.html")
     * @Method("GET")
     * @Template()
     */
    public function fileManagerAction(ResourceEntity $resource, Request $request) {
        $this->ensureCanManageFiles($resource);
        $templateData = ['resource' => $resource, 'godMode' => (bool)$request->get('god', false)];
        if ($metadataId = $request->get('metadataId')) {
            $templateData['metadata'] = $this->metadataRepository->findByNameOrId($metadataId);
        }
        return $templateData;
    }

    /**
     * @Route("/{resource}/file-manager")
     * @Method({"GET", "POST", "PUT"})
     */
    public function fileConnectorAction(ResourceEntity $resource, Request $request) {
        $this->ensureCanManageFiles($resource);
        $godMode = $request->get('god');
        $readOnly = false;
        if ($godMode) {
            $this->ensureHasRole($resource, SystemRole::ADMIN());
        } else {
            $readOnly = $resource->hasWorkflow();
            if ($readOnly) {
                $availableTransitions = $resource->getWorkflow()->getTransitions($resource);
                $blockedTransitions = $this->resourceNormalizer->getBlockedTransitions($resource, $this->getUser());
                $readOnly = count($availableTransitions) + 1 == count($blockedTransitions); // +1 - add EDIT transition
            }
        }
        $uploadDirs = $this->getUploadDirs($resource);
        $roots = array_map(
            function (array $uploadDir) use ($readOnly, $godMode) {
                $dirSpec = [
                    'id' => $uploadDir['id'],
                    'driver' => 'LocalFileSystem',
                    'path' => $uploadDir['path'],
                    'alias' => $uploadDir['label'],
                    'URL' => 'file/' . $uploadDir['id'],
                    'winHashFix' => DIRECTORY_SEPARATOR !== '/',
                    'uploadAllow' => ['all'],
                    'uploadOrder' => ['allow', 'deny'],
                    'accessControl' => [$this, 'filesFilter'],
                    'tmbURL' => 'thumbnail',
                    'disabled' => 'netmount,getfile,hide',
                    'attributes' => [],
                ];
                if (!$uploadDir['canBeUsedInResources']) {
                    $dirSpec['disabled'] .= ',addFilesAsMetadata';
                } else {
                    if (!$godMode) {
                        $dirSpec['disabled'] .= ',rename,resize,rm,cut,edit,empty';
                        $dirSpec['attributes'][] = ['pattern' => '/.*/', 'locked' => true];
                    }
                    if ($readOnly) {
                        $dirSpec['disabled'] .= ',mkdir,mkfile,archive,extract,undo,redo,copy,paste,addFilesAsMetadata';
                        $dirSpec['uploadDeny'] = ['all'];
                    }
                }
                $dirSpec['disabled'] = array_unique(explode(',', $dirSpec['disabled']));
                return $dirSpec;
            },
            $uploadDirs
        );
        $opts = ['debug' => true, 'roots' => $roots];
        $connector = new elFinderConnector(new elFinder($opts));
        $connector->run();
    }

    /**
     * @Route("/{resource}/thumbnail/{filepath}")
     * @Method("GET")
     */
    public function thumbnailAction(ResourceEntity $resource, string $filepath) {
        // example thumbnail path: lresourceFiles_SU1HXzIwMTgxMDE0XzE1NDI0OC5KUEc1544087790.png
        preg_match('#l(.+)_(.+)#', $filepath, $matches);
        if ($matches) {
            return $this->fileAction($resource, $matches[1], '.tmb/' . $filepath);
        } elseif (strpos($filepath, 'temp_') === 0) {
            // temp files can be in any directory... look for them.
            $uploadDirs = $this->getUploadDirs($resource);
            foreach ($uploadDirs as $uploadDir) {
                if (file_exists($uploadDir['path'] . '/.tmb/' . $filepath)) {
                    return $this->fileAction($resource, $uploadDir['id'], '.tmb/' . $filepath);
                }
            }
        }
        throw $this->createNotFoundException();
    }

    /**
     * @Route("/{resource}/file/{uploadDirId}/{filepath}", requirements={"filepath"=".*"})
     * @Method("GET")
     */
    public function fileAction(ResourceEntity $resource, string $uploadDirId, string $filepath) {
        $this->ensureCanManageFiles($resource);
        $uploadDirs = $this->getUploadDirs($resource);
        foreach ($uploadDirs as $uploadDir) {
            if ($uploadDir['id'] == $uploadDirId) {
                $thumbPath = $uploadDir['path'] . '/' . $filepath;
                if (file_exists($thumbPath)) {
                    return new BinaryFileResponse($thumbPath);
                }
            }
        }
        throw $this->createNotFoundException();
    }

    /**
     * Current implementation: do not show files and folders which names start with "."
     * @see https://github.com/Studio-42/elFinder/blob/1a7ecc0af1b67cf476d8a076b9253348af4be475/php/connector.php-dist#L284-L288
     * @inheritdoc
     */
    public function filesFilter($attr, $path, $data, $volume, $isDir, $relpath) {
        $basename = basename($path);
        return $basename[0] === '.' && strlen($relpath) !== 1 ? !($attr == 'read' || $attr == 'write') : null;
    }

    private function hasRole(ResourceEntity $resource, SystemRole $role): bool {
        $requiredRole = $role->roleName($resource->getResourceClass());
        return $this->getUser()->hasRole($requiredRole);
    }

    private function ensureHasRole(ResourceEntity $resource, SystemRole $role): void {
        if (!$this->hasRole($resource, $role)) {
            throw $this->createAccessDeniedException();
        }
    }

    private function ensureCanManageFiles(ResourceEntity $resource): void {
        $this->ensureHasRole($resource, SystemRole::OPERATOR());
    }

    private function getUploadDirs(ResourceEntity $resource): array {
        $uploadDirs = $this->container->getParameter('repeka.upload_dirs');
        $uploadDirs = array_map(
            function (array $uploadDir) use ($resource) {
                $uploadDir['path'] = $this->displayStrategyEvaluator->render($resource, $uploadDir['path']);
                return $uploadDir;
            },
            $uploadDirs
        );
        foreach ($uploadDirs as &$uploadDir) {
            if (!file_exists($uploadDir['path'])) {
                $this->mkdirRecursive($uploadDir['path']);
            }
            $uploadDir['path'] = realpath($uploadDir['path']);
        }
        return $uploadDirs;
    }

    private function mkdirRecursive(string $path) {
        if (!file_exists($path)) {
            $result = mkdir($path, 0750, true);
            Assertion::true($result, 'Could not create upload dir: ' . $path);
        }
    }
}
