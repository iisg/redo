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

    public function exposeResourceAction(
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
        if (!$content) {
            $response = new Response('', Response::HTTP_NOT_FOUND);
        } else {
            if ($endpointUsageTrackingKey) {
                $endpointUsageLogEntry = new EndpointUsageLogEntry($request, $resource, $endpointUsageTrackingKey);
                $this->entityManager->persist($endpointUsageLogEntry);
                $this->entityManager->flush();
            }
            $response = new Response($content, Response::HTTP_OK);
        }
        $response->headers->add($headers);
        return $response;
    }
}
