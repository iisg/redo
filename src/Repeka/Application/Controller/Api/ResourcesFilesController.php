<?php
namespace Repeka\Application\Controller\Api;

use elFinder;
use elFinderConnector;
use Repeka\Application\Serialization\ResourceNormalizer;
use Repeka\Domain\Constants\SystemRole;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Service\ResourceDisplayStrategyEvaluator;
use Repeka\Domain\Service\ResourceFileStorage;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/resources")
 */
class ResourcesFilesController extends ApiController {
    public static $fileManagerConnectorClassName = elFinderConnector::class;

    /** @var ResourceDisplayStrategyEvaluator */
    private $displayStrategyEvaluator;
    /** @var MetadataRepository */
    private $metadataRepository;
    /** @var ResourceNormalizer */
    private $resourceNormalizer;
    /** @var ResourceFileStorage */
    private $resourceFileStorage;

    public function __construct(
        ResourceDisplayStrategyEvaluator $displayStrategyEvaluator,
        MetadataRepository $metadataRepository,
        ResourceNormalizer $resourceNormalizer,
        ResourceFileStorage $resourceFileStorage
    ) {
        $this->displayStrategyEvaluator = $displayStrategyEvaluator;
        $this->metadataRepository = $metadataRepository;
        $this->resourceNormalizer = $resourceNormalizer;
        $this->resourceFileStorage = $resourceFileStorage;
    }

    /**
     * @Route("/{resource}/file-manager.html")
     * @Method("GET")
     * @Template()
     * @Security("is_granted('METADATA_VISIBILITY', resource)")
     */
    public function fileManagerAction(ResourceEntity $resource, Request $request) {
        $templateData = ['resource' => $resource, 'godMode' => (bool)$request->get('god', false)];
        if ($metadataId = $request->get('metadataId')) {
            $templateData['metadata'] = $this->metadataRepository->findByNameOrId($metadataId);
        }
        return $templateData;
    }

    /**
     * @Route("/{resource}/file-manager")
     * @Method({"GET", "POST", "PUT"})
     * @Security("is_granted('METADATA_VISIBILITY', resource)")
     */
    public function fileConnectorAction(ResourceEntity $resource, Request $request) {
        $godMode = $request->get('god');
        $readOnly = false;
        if ($godMode) {
            $this->denyAccessUnlessGranted(SystemRole::ADMIN(), $resource);
        } else {
            $readOnly = $resource->hasWorkflow();
            if ($readOnly) {
                $availableTransitions = $resource->getWorkflow()->getTransitions($resource);
                $blockedTransitions = $this->resourceNormalizer->getBlockedTransitions($resource, $this->getUser());
                $readOnly = count($availableTransitions) + 1 == count($blockedTransitions); // +1 - add EDIT transition
            } else {
                $this->denyAccessUnlessGranted(SystemRole::OPERATOR(), $resource);
            }
        }
        $uploadDirs = $this->resourceFileStorage->uploadDirsForResource($resource);
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
        $opts = ['debug' => false, 'roots' => $roots];
        $fileManagerConnectorClassName = self::$fileManagerConnectorClassName; // line for the linter
        $connector = new $fileManagerConnectorClassName(new elFinder($opts));
        $connector->run();
        return $this->createJsonResponse(null);
    }

    /**
     * @Route("/{resource}/thumbnail/{filepath}")
     * @Method("GET")
     * @Security("is_granted('METADATA_VISIBILITY', resource)")
     */
    public function thumbnailAction(ResourceEntity $resource, string $filepath) {
        // example thumbnail path: lresourceFiles_SU1HXzIwMTgxMDE0XzE1NDI0OC5KUEc1544087790.png
        preg_match('#l(.+)_(.+)#', $filepath, $matches);
        if ($matches) {
            return $this->fileAction($resource, $matches[1] . '/.tmb/' . $filepath);
        } elseif (strpos($filepath, 'temp_') === 0) {
            // temp files can be in any directory... look for them.
            $uploadDirs = $this->resourceFileStorage->uploadDirsForResource($resource);
            foreach ($uploadDirs as $uploadDir) {
                $possibleThumbPath = $uploadDir['path'] . '/.tmb/' . $filepath;
                if (file_exists($possibleThumbPath)) {
                    return $this->fileAction($resource, $possibleThumbPath);
                }
            }
        }
        throw $this->createNotFoundException();
    }

    /**
     * @Route("/{resource}/file/{filepath}", requirements={"filepath"=".*"})
     * @Method("GET")
     * @Security("is_granted('METADATA_VISIBILITY', resource)")
     */
    public function fileAction(ResourceEntity $resource, string $filepath) {
        $filepath = $this->resourceFileStorage->getFileSystemPath($resource, $filepath);
        if (file_exists($filepath)) {
            return new BinaryFileResponse($filepath);
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
}
