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
                        $endpoint['fts']['searchable_metadata_ids'] ?? [],
                        $endpoint['fts']['searchable_resource_classes'] ?? [],
                        $endpoint['headers']
                    )
                );
            } else {
                $builder->addRoute(
                    new SingleResourceExposedEndpointRoute(
                        $path,
                        $endpoint['metadata'] ?? null,
                        $endpoint['resourceId'],
                        $endpoint['headers']
                    )
                );
            }
        }
        return $builder->build();
    }
}
