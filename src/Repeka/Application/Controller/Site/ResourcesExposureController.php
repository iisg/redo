<?php
namespace Repeka\Application\Controller\Site;

use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\EndpointUsageLogRepository;
use Repeka\Domain\UseCase\EndpointUsageLog\EndpointUsageLogCreateCommand;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ResourcesExposureController extends Controller {
    use CommandBusAware;
    private $endpointUsageLogRepository;

    public function __construct(EndpointUsageLogRepository $endpointUsageLogRepository) {
        $this->endpointUsageLogRepository = $endpointUsageLogRepository;
    }

    public function exposeResourceMetadataAction(
        ResourceEntity $resourceId,
        $metadataNameOrId,
        array $headers,
        ?string $endpointUsageTrackingKey,
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
            $this->trackUsage($endpointUsageTrackingKey, $request, $resource);
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
        ?string $endpointUsageTrackingKey,
        Request $request
    ) {
        // $resourceId parameter name is required to write understandable URLs in config, like /resources/{resourceId}/export_type
        /** @var ResourceEntity $resource */
        $resource = $resourceId;
        $responseData = ['r' => $resource, 'resource' => $resource];
        $this->trackUsage($endpointUsageTrackingKey, $request, $resource);
        $response = $this->render($template, $responseData);
        if ($response->getContent()) {
            $response->headers->add($headers);
            return $response;
        } else {
            throw $this->createNotFoundException();
        }
    }

    private function trackUsage($endpointUsageTrackingKey, $request, $resource) {
        if ($endpointUsageTrackingKey) {
            $this->handleCommand(new EndpointUsageLogCreateCommand($request, $endpointUsageTrackingKey, $resource));
        }
    }
}
