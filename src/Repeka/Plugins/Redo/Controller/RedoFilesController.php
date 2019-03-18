<?php
namespace Repeka\Plugins\Redo\Controller;

use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\UseCase\EndpointUsageLog\EndpointUsageLogCreateCommand;
use Repeka\Domain\UseCase\Resource\ResourceGodUpdateCommand;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

class RedoFilesController extends Controller {
    use CommandBusAware;

    private $downloadConfig;

    public function __construct(array $downloadConfig) {
        $this->downloadConfig = $downloadConfig;
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
            $this->handleCommand(new EndpointUsageLogCreateCommand($request, 'resourceDownload', $resource));
            try {
                if (isset($this->downloadConfig['resource_download_metadata'])) {
                    $downloadCountMetadata = $resource->getKind()->getMetadataByIdOrName(
                        $this->downloadConfig['resource_download_metadata']
                    );
                    $updatedResourceContents = $this->updateResourceDownloadCount($resource->getContents(), $downloadCountMetadata);
                    $resourceUpdateCommand = ResourceGodUpdateCommand::builder()
                        ->setResource($resource)
                        ->setNewContents($updatedResourceContents);
                    $this->commandBus->handle($resourceUpdateCommand->build());
                }
            } catch (EntityNotFoundException $e) {
            };
        }
        return $response;
    }

    private function updateResourceDownloadCount(ResourceContents $resourceContents, Metadata $metadata): ResourceContents {
        $metadataValues = $resourceContents->getValues($metadata);
        $metadataValue = $metadataValues ? $metadataValues[0]->getValue() : 0;
        return $resourceContents->withReplacedValues($metadata, [['value' => $metadataValue + 1]]);
    }
}
