<?php
namespace Repeka\Website\Controller;

use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Application\Cqrs\Middleware\FirewallMiddleware;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\UseCase\Resource\ResourceEvaluateDisplayStrategiesCommand;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class WebsiteResourcesController extends Controller {
    use CommandBusAware;

    public function resourceViewAction(ResourceEntity $resource, $metadataNameOrId, array $headers) {
        /** @var ResourceEntity $resource */
        $resource = FirewallMiddleware::bypass(
            function () use ($resource) {
                return $this->handleCommand(new ResourceEvaluateDisplayStrategiesCommand($resource));
            }
        );
        $metadata = $resource->getKind()->getMetadataByIdOrName($metadataNameOrId);
        $content = implode('', $resource->getValues($metadata));
        if (!$content) {
            $response = new Response('', Response::HTTP_NOT_FOUND);
        } else {
            $response = new Response($content, Response::HTTP_OK);
        }
        $response->headers->add($headers);
        return $response;
    }
}
