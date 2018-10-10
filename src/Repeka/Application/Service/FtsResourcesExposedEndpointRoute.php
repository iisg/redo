<?php
namespace Repeka\Application\Service;

use Assert\Assertion;
use Repeka\Application\Controller\Site\ResourcesSearchController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

class FtsResourcesExposedEndpointRoute extends Route {
    public function __construct(
        string $path,
        string $template,
        array $ftsConfig,
        array $headers = []
    ) {
        Assertion::notEmpty($template, 'template must be set for search resource exposed endpoint: ' . $path);
        parent::__construct(
            $path,
            [
                '_controller' => ResourcesSearchController::class . ':searchResourcesAction',
                'template' => $template,
                'ftsConfig' => $ftsConfig,
                'headers' => $headers,
            ],
            [],
            [],
            '',
            [],
            [Request::METHOD_GET]
        );
    }
}
