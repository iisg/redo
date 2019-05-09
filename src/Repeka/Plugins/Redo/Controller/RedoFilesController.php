<?php
namespace Repeka\Plugins\Redo\Controller;

use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Application\Repository\Transactional;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Service\ResourceFileStorage;
use Repeka\Domain\UseCase\EndpointUsageLog\EndpointUsageLogCreateCommand;
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
        $this->denyAccessUnlessGranted('FILE_DOWNLOAD', ['resource' => $resource, 'filepath' => $filepath]);
        $response = $this->forward(
            'Repeka\Application\Controller\Api\ResourcesFilesController::fileAction',
            ['resource' => $resource, 'filepath' => $filepath]
        );
        if ($response->getStatusCode() == 200) {
            $this->logEndpointUsage($resource, $request);
        }
        return $response;
    }

    /**
     * @Route("/redo/resources/{resource}/archive/{path}", requirements={"path"=".*"})
     * @Method("GET")
     * @Security("is_granted('METADATA_VISIBILITY', resource)")
     */
    public function streamCompressedFileAction(Request $request, ResourceEntity $resource, string $path) {
        $this->denyAccessUnlessGranted('FILE_DOWNLOAD', ['resource' => $resource, 'filepath' => $path]);
        $response = new StreamedResponse(
            function () use ($path, $resource) {
                $outputName = substr($path, strpos($path, '/') + 1) . '.zip';
                $this->createZipFile($this->resourceFileStorage->getFileSystemPath($resource, $path), $outputName);
            }
        );
        $this->logEndpointUsage($resource, $request);
        return $response;
    }

    public function createZipFile(string $path, string $outputName) {
        header("Content-Type: application/zip");
        header("Content-disposition: attachment; filename=$outputName");
        $fp = popen("zip -1jr - $path", "r");
        $chunkSize = 8192;
        while (!feof($fp)) {
            $buff = fread($fp, $chunkSize);
            echo $buff;
        }
        pclose($fp);
    }

    private function logEndpointUsage(ResourceEntity $resource, Request $request) {
        $this->handleCommand(new EndpointUsageLogCreateCommand($request, 'resourceDownload', $resource));
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
