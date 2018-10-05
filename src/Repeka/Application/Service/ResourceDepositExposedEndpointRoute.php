<?php
namespace Repeka\Application\Service;

use Assert\Assertion;
use Repeka\Application\Controller\Site\ResourceDepositController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

class ResourceDepositExposedEndpointRoute extends Route {
    public function __construct(
        $path,
        string $template,
        array $depositConfig,
        array $headers = []
    ) {
        Assertion::notNull($template, 'template must be set for exposed endpoint: ' . $path);
        parent::__construct(
            $path,
            [
                '_controller' => ResourceDepositController::class . ':depositAction',
                'template' => $template,
                'depositConfig' => $depositConfig,
                'headers' => $headers,
                'phase' => '',
            ],
            [],
            [],
            '',
            [],
            [Request::METHOD_GET]
        );
    }
}
