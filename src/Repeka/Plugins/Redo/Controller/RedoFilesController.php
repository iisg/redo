<?php
namespace Repeka\Plugins\Redo\Controller;

use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Application\Repository\Transactional;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\EndpointUsageLog\EndpointUsageLogCreateCommand;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class RedoFilesController extends Controller {
    private const DOWNLOAD_COUNT_METADATA_NAME = 'resource_downloads';

    use CommandBusAware;
    use Transactional;

    /** @var ResourceRepository */
    private $resourceRepository;

    public function __construct(ResourceRepository $resourceRepository) {
        $this->resourceRepository = $resourceRepository;
    }

    /**
     * @Route("/redo/resources/{resource}/file/{filepath}", requirements={"filepath"=".*"})
     * @Method("GET")
     * @Security("is_granted('METADATA_VISIBILITY', resource)")
     */
    public function fileAction(Request $request, ResourceEntity $resource, string $filepath) {
        $response = $this->forward(
            'Repeka\Application\Controller\Api\ResourcesFilesController::fileAction',
            ['resource' => $resource, 'filepath' => $filepath]
        );
        if ($response->getStatusCode() == 200) {
            $this->handleCommand(new EndpointUsageLogCreateCommand($request, 'resourceDownload', $resource));
            if ($resource->getKind()->hasMetadata(self::DOWNLOAD_COUNT_METADATA_NAME)) {
                $this->transactional(
                    function () use ($resource) {
                        $this->incrementResourceDownloadCount($resource);
                    }
                );
            }
        }
        return $response;
    }

    private function incrementResourceDownloadCount(ResourceEntity $resource) {
        $downloadCountMetadata = $resource->getKind()->getMetadataByName(self::DOWNLOAD_COUNT_METADATA_NAME);
        $currentCount = $resource->getValuesWithoutSubmetadata($downloadCountMetadata)[0] ?? 0;
        $updatedResourceContents = $resource->getContents()->withReplacedValues($downloadCountMetadata, $currentCount + 1);
        $resource->updateContents($updatedResourceContents);
        $this->resourceRepository->save($resource);
    }
}
