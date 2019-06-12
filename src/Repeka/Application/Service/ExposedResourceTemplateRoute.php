<?php
namespace Repeka\Application\Service;

use Repeka\Application\Controller\Site\ResourcesExposureController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

class ExposedResourceTemplateRoute extends Route {
    public function __construct(
        string $path,
        string $template,
        ?int $resourceId,
        ?string $statsEventName,
        ?string $statsEventGroup,
        array $headers = []
    ) {
        parent::__construct(
            $path,
            [
                '_controller' => ResourcesExposureController::class . ':exposeResourceTemplateAction',
                'template' => $template,
                'headers' => $headers,
                'statsEventName' => $statsEventName,
                'statsEventGroup' => $statsEventGroup,
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
