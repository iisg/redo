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
            if (isset($endpoint['fts'])) {
                $builder->addRoute(
                    new FtsResourcesExposedEndpointRoute(
                        $path,
                        $endpoint['template'] ?? '',
                        $endpoint['fts'],
                        $endpoint['headers']
                    )
                );
            } elseif (isset($endpoint['deposit'])) {
                $builder->addRoute(
                    new ResourceDepositExposedEndpointRoute($path, $endpoint['template'] ?? '', $endpoint['deposit'], $endpoint['headers'])
                );
            } elseif (isset($endpoint['metadata'])) {
                $builder->addRoute(
                    new ExposedResourceMetadataRoute(
                        $path,
                        $endpoint['metadata'] ?? null,
                        $endpoint['resourceId'],
                        $endpoint['endpointUsageTrackingKey'],
                        $endpoint['headers']
                    )
                );
            } else {
                $builder->addRoute(
                    new ExposedResourceTemplateRoute(
                        $path,
                        $endpoint['template'] ?? '',
                        $endpoint['resourceId'],
                        $endpoint['endpointUsageTrackingKey'],
                        $endpoint['headers']
                    )
                );
            }
        }
        return $builder->build();
    }
}
