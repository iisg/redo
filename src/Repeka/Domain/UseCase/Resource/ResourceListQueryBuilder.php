<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Entity\ResourceKind;

class ResourceListQueryBuilder {
    private $resourceKinds = [];
    private $resourceClasses = [];
    private $onlyTopLevel = false;

    /**
     * @param ResourceKind[] $resourceKinds
     * @return ResourceListQueryBuilder
     */
    public function filterByResourceKinds(array $resourceKinds): ResourceListQueryBuilder {
        $this->resourceKinds = array_values(array_merge($this->resourceKinds, $resourceKinds));
        return $this;
    }

    public function filterByResourceKind(ResourceKind $resourceKind): ResourceListQueryBuilder {
        return $this->filterByResourceKinds([$resourceKind]);
    }

    /**
     * @param string $resourceClasses
     * @return ResourceListQueryBuilder
     */
    public function filterByResourceClasses(array $resourceClasses): ResourceListQueryBuilder {
        $this->resourceClasses = array_values(array_unique(array_merge($this->resourceClasses, $resourceClasses)));
        return $this;
    }

    public function filterByResourceClass(string $resourceClass): ResourceListQueryBuilder {
        return $this->filterByResourceClasses([$resourceClass]);
    }

    public function onlyTopLevel(): ResourceListQueryBuilder {
        $this->onlyTopLevel = true;
        return $this;
    }

    public function build(): ResourceListQuery {
        return ResourceListQuery::withParams($this->resourceClasses, $this->resourceKinds, $this->onlyTopLevel);
    }
}
