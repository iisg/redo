<?php
namespace Repeka\Application\Service;

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouteCollectionBuilder;

class ResourcesEndpointsExposer {
    /** @var array */
    private $exposedEndpoints;

    public function __construct(array $exposedEndpoints) {
        $this->exposedEndpoints = $exposedEndpoints;
    }

    public function loadRoutes(): RouteCollection {
        $builder = new RouteCollectionBuilder();
        foreach ($this->exposedEndpoints as $path => $endpoint) {
            $builder->addRoute(
                new SingleResourceExposedEndpointRoute($path, $endpoint['metadata'], $endpoint['resourceId'], $endpoint['headers'])
            );
        }
        return $builder->build();
    }
}
