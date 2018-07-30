<?php
namespace Repeka\Application\Service;

use Repeka\Application\Controller\Site\ResourcesExposureController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

class SingleResourceExposedEndpointRoute extends Route {
    public function __construct(string $path, $metadataNameOrId, ?int $resourceId = null, array $headers = []) {
        parent::__construct(
            $path,
            [
                '_controller' => ResourcesExposureController::class . ':exposeResourceAction',
                'metadataNameOrId' => $metadataNameOrId,
                'headers' => $headers,
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
