<?php
namespace Repeka\Application\Controller\Site;

use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\EventLogRepository;
use Repeka\Domain\UseCase\Stats\EventLogCreateCommand;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class ResourcesExposureController extends Controller {
    use CommandBusAware;
    private $eventLogRepository;

    public function __construct(EventLogRepository $eventLogRepository) {
        $this->eventLogRepository = $eventLogRepository;
    }

    public function exposeResourceMetadataAction(
        ResourceEntity $resourceId,
        $metadataNameOrId,
        array $headers,
        ?string $statsEventName,
        ?string $statsEventGroup
    ) {
        // $resourceId parameter name is required to write understandable URLs in config, like /resources/{resourceId}
        /** @var ResourceEntity $resource */
        $resource = $resourceId;
        $content = '';
        try {
            $metadata = $resource->getKind()->getMetadataByIdOrName($metadataNameOrId);
            $content = implode('', $resource->getValues($metadata));
        } catch (\Throwable $e) {
        }
        if ($content) {
            $this->trackUsage($statsEventName, $statsEventGroup, $resource);
            $response = new Response($content, Response::HTTP_OK);
            $response->headers->add($headers);
            return $response;
        } else {
            throw $this->createNotFoundException();
        }
    }

    public function exposeResourceTemplateAction(
        ResourceEntity $resourceId,
        string $template,
        array $headers,
        ?string $statsEventName,
        ?string $statsEventGroup
    ) {
        /** @var ResourceEntity $resource */
        $resource = $resourceId;
        $responseData = ['r' => $resource, 'resource' => $resource];
        $this->trackUsage($statsEventName, $statsEventGroup, $resource);
        $response = $this->render($template, $responseData);
        $this->denyAccessUnlessGranted(['METADATA_VISIBILITY'], $resource);
        if ($response->getContent()) {
            $response->headers->add($headers);
            return $response;
        } else {
            throw $this->createNotFoundException();
        }
    }

    private function trackUsage(?string $eventName, ?string $eventGroup = null, ?ResourceEntity $resource = null): void {
        if ($eventName) {
            $this->handleCommand(new EventLogCreateCommand($eventName, $eventGroup, $resource));
        }
    }
}
