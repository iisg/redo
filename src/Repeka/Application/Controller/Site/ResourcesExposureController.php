<?php
namespace Repeka\Application\Controller\Site;

use Doctrine\ORM\EntityManagerInterface;
use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Domain\Entity\EndpointUsageLogEntry;
use Repeka\Domain\Entity\ResourceEntity;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ResourcesExposureController extends Controller {
    use CommandBusAware;
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
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
        $responseData = ['resource' => $resource];
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
            $endpointUsageLogEntry = new EndpointUsageLogEntry($request, $resource, $endpointUsageTrackingKey);
            $this->entityManager->persist($endpointUsageLogEntry);
            $this->entityManager->flush();
        }
    }
}
