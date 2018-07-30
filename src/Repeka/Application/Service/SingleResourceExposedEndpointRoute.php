<?php
namespace Repeka\Application\Service;

use Repeka\Website\Controller\WebsiteResourcesController;
use Symfony\Component\Routing\Route;

class SingleResourceExposedEndpointRoute extends Route {
    public function __construct(string $path, $metadataNameOrId, ?int $resourceId = null, array $headers = []) {
        parent::__construct(
            $path,
            [
                '_controller' => WebsiteResourcesController::class . ':resourceViewAction',
                'metadataNameOrId' => $metadataNameOrId,
                'headers' => $headers,
            ]
        );
        if ($resourceId) {
            $this->setDefault('resource', $resourceId);
        }
    }
}
