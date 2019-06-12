<?php
namespace Repeka\Plugins\Redo\Controller;

use ReCaptcha\ReCaptcha;
use Repeka\Application\Controller\Api\ResourcesFilesController;
use Repeka\Application\Controller\Site\ResourcesExposureController;
use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Application\Repository\Transactional;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Service\ResourceFileStorage;
use Repeka\Domain\UseCase\Stats\EventLogCreateCommand;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RedoFilesController extends Controller {
    private const DOWNLOAD_COUNT_METADATA_NAME = 'resource_downloads';

    use CommandBusAware;
    use Transactional;

    /** @var ResourceRepository */
    private $resourceRepository;
    /** @var ResourceFileStorage */
    private $resourceFileStorage;

    public function __construct(ResourceRepository $resourceRepository, ResourceFileStorage $resourceFileStorage) {
        $this->resourceRepository = $resourceRepository;
        $this->resourceFileStorage = $resourceFileStorage;
    }

    /**
     * @Route("/redo/resources/{resource}/file/{filepath}", requirements={"filepath"=".*"})
     * @Method("GET")
     * @Security("is_granted('METADATA_VISIBILITY', resource)")
     */
    public function fileAction(Request $request, ResourceEntity $resource, string $filepath) {
        $response = $this->forward(ResourcesFilesController::class . '::fileAction', ['resource' => $resource, 'filepath' => $filepath]);
        if ($response->isSuccessful()) {
            $this->logEvent($resource, $request);
        }
        return $response;
    }

    /**
     * @Route("/resources/{resource}/browse")
     * @Method("GET")
     */
    public function browseAction(Request $request, ResourceEntity $resource) {
        $response = $this->forward(
            ResourcesExposureController::class . '::exposeResourceTemplateAction',
            [
                'request' => $request,
                'resourceId' => $resource,
                'headers' => [],
                'template' => 'redo/resource-details/image-reader.twig',
                'statsEventName' => 'resourceBrowse',
            ]
        );
        if ($response->isSuccessful()) {
            $this->logEvent($resource, $request);
        }
        return $response;
    }

    /**
     * @Route("/redo/resources/{resource}/archive/{path}", requirements={"path"=".*"}, methods={"POST"})
     * @Security("is_granted('METADATA_VISIBILITY', resource)")
     */
    public function streamCompressedFileAction(Request $request, ResourceEntity $resource, string $path) {
        $this->denyAccessUnlessGranted('FILE_DOWNLOAD', ['resource' => $resource, 'filepath' => $path]);
        if ($this->captchaVerified($request)) {
            ini_set('max_execution_time', 2000); // 2000 allows to passthru approx 200GB archive
            $response = new StreamedResponse(
                function () use ($path, $resource) {
                    $outputName = substr($path, strpos($path, '/') + 1) . '.zip';
                    $this->createZipFile($this->resourceFileStorage->getFileSystemPath($resource, $path), $outputName);
                }
            );
            $this->logEvent($resource, $request);
            return $response;
        }
        return $this->redirect('/resources/' . $resource->getId());
    }

    private function captchaVerified(Request $request): bool {
        $recaptcha = new ReCaptcha($this->getParameter('redo.captcha_private_key'));
        $captchaParams = $request->request->get('g-recaptcha-response');
        $response = $recaptcha->verify($captchaParams, $request->getClientIp());
        return $response->isSuccess();
    }

    public function createZipFile(string $path, string $outputName) {
        header("Content-Type: application/zip");
        header("Content-disposition: attachment; filename=$outputName");
        $fp = popen("zip -0jr - $path", "r");
        $chunkSize = 81920;
        while (!feof($fp)) {
            $buff = fread($fp, $chunkSize);
            echo $buff;
        }
        pclose($fp);
    }

    private function logEvent(ResourceEntity $resource, Request $request) {
        $this->handleCommand(new EventLogCreateCommand($request, 'resourceDownload', $resource));
        if ($resource->getKind()->hasMetadata(self::DOWNLOAD_COUNT_METADATA_NAME)) {
            $this->transactional(
                function () use ($resource) {
                    $this->incrementResourceDownloadCount($resource);
                }
            );
        }
    }

    private function incrementResourceDownloadCount(ResourceEntity $resource) {
        $downloadCountMetadata = $resource->getKind()->getMetadataByName(self::DOWNLOAD_COUNT_METADATA_NAME);
        $currentCount = $resource->getValuesWithoutSubmetadata($downloadCountMetadata)[0] ?? 0;
        $updatedResourceContents = $resource->getContents()->withReplacedValues($downloadCountMetadata, $currentCount + 1);
        $resource->updateContents($updatedResourceContents);
        $this->resourceRepository->save($resource);
    }
}
