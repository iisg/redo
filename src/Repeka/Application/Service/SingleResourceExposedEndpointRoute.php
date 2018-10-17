<?php
namespace Repeka\Application\Service;

use Assert\Assertion;
use Repeka\Application\Controller\Site\ResourcesExposureController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

class SingleResourceExposedEndpointRoute extends Route {
    public function __construct(
        string $path,
        $metadataNameOrId,
        ?int $resourceId,
        ?string $endpointUsageTrackingKey,
        array $headers = []
    ) {
        Assertion::notNull($metadataNameOrId, 'metadataId must be set for single resource exposed endpoint: ' . $path);
        parent::__construct(
            $path,
            [
                '_controller' => ResourcesExposureController::class . ':exposeResourceAction',
                'metadataNameOrId' => $metadataNameOrId,
                'headers' => $headers,
                'endpointUsageTrackingKey' => $endpointUsageTrackingKey,
            ],
            [],
            [],
            '',
            [],
            [Request::METHOD_GET]
        );
        if ($resourceId) {
            $this->setDefault('resourceId', $resourceId);
        }
    }
}
