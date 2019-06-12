<?php
namespace Repeka\Application\Controller\Site;

use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\EventLogRepository;
use Repeka\Domain\UseCase\Stats\EventLogCreateCommand;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
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
        Request $request
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
            $this->trackUsage($statsEventName, $request, $resource);
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
        Request $request
    ) {
        // $resourceId parameter name is required to write understandable URLs in config, like /resources/{resourceId}/export_type
        /** @var ResourceEntity $resource */
        $resource = $resourceId;
        $responseData = ['r' => $resource, 'resource' => $resource];
        $this->trackUsage($statsEventName, $request, $resource);
        $response = $this->render($template, $responseData);
        $this->denyAccessUnlessGranted(['METADATA_VISIBILITY'], $resource);
        if ($response->getContent()) {
            $response->headers->add($headers);
            return $response;
        } else {
            throw $this->createNotFoundException();
        }
    }

    private function trackUsage($statsEventName, $request, $resource) {
        if ($statsEventName) {
            $this->handleCommand(new EventLogCreateCommand($request, $statsEventName, $resource));
        }
    }
}
