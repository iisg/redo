<?php
namespace Repeka\Application\Controller\Site;

use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Application\Cqrs\Middleware\FirewallMiddleware;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\UseCase\Resource\ResourceEvaluateDisplayStrategiesCommand;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class ResourcesExposureController extends Controller {
    use CommandBusAware;

    public function exposeResourceAction(ResourceEntity $resourceId, $metadataNameOrId, array $headers) {
        /** @var ResourceEntity $resource */
        $resource = FirewallMiddleware::bypass(
            function () use ($resourceId) {
                return $this->handleCommand(new ResourceEvaluateDisplayStrategiesCommand($resourceId));
            }
        );
        $content = '';
        try {
            $metadata = $resource->getKind()->getMetadataByIdOrName($metadataNameOrId);
            $content = implode('', $resource->getValues($metadata));
        } catch (\Throwable $e) {
        }
        if (!$content) {
            $response = new Response('', Response::HTTP_NOT_FOUND);
        } else {
            $response = new Response($content, Response::HTTP_OK);
        }
        $response->headers->add($headers);
        return $response;
    }
}
