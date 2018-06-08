<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\UseCase\Audit\AbstractListQueryBuilder;

/** @SuppressWarnings(PHPMD.TooManyPublicMethods) */
class ResourceListQueryBuilder extends AbstractListQueryBuilder {
    private $resourceKinds = [];
    private $resourceClasses = [];
    private $sortBy = [];
    private $parentId = 0;
    private $contents = [];
    private $onlyTopLevel = false;
    private $ids = [];
    private $workflowPlacesIds = [];

    /** @param ResourceKind[] | int[] $resourceKinds */
    public function filterByResourceKinds(array $resourceKinds): ResourceListQueryBuilder {
        $this->resourceKinds = array_values(array_merge($this->resourceKinds, $resourceKinds));
        return $this;
    }

    public function filterByResourceKind(ResourceKind $resourceKind): ResourceListQueryBuilder {
        return $this->filterByResourceKinds([$resourceKind]);
    }

    public function filterByResourceClasses(array $resourceClasses): ResourceListQueryBuilder {
        $this->resourceClasses = array_values(array_unique(array_merge($this->resourceClasses, $resourceClasses)));
        return $this;
    }

    public function filterByResourceClass(string $resourceClass): ResourceListQueryBuilder {
        return $this->filterByResourceClasses([$resourceClass]);
    }

    public function filterByParentId(int $parentId): ResourceListQueryBuilder {
        $this->parentId = $parentId;
        return $this;
    }

    public function sortBy(array $sortBy): ResourceListQueryBuilder {
        $this->sortBy = array_replace($this->sortBy, $sortBy);
        return $this;
    }

    /** @param ResourceContents|array $contents */
    public function filterByContents($contents): ResourceListQueryBuilder {
        $this->contents = $contents;
        return $this;
    }

    public function filterByIds(array $ids): ResourceListQueryBuilder {
        $this->ids = $ids;
        return $this;
    }

    public function onlyTopLevel(): ResourceListQueryBuilder {
        $this->onlyTopLevel = true;
        return $this;
    }

    public function filterByWorkflowPlacesIds(array $workflowPlacesIds): ResourceListQueryBuilder {
        $this->workflowPlacesIds = $workflowPlacesIds;
        return $this;
    }

    public function build(): ResourceListQuery {
        return ResourceListQuery::withParams(
            $this->ids,
            $this->resourceClasses,
            $this->resourceKinds,
            $this->sortBy,
            $this->parentId,
            $this->contents instanceof ResourceContents ? $this->contents : ResourceContents::fromArray($this->contents),
            $this->onlyTopLevel,
            $this->page,
            $this->resultsPerPage,
            $this->workflowPlacesIds
        );
    }
}
