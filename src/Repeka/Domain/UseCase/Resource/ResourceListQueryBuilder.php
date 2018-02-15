<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceKind;

class ResourceListQueryBuilder {
    private $resourceKinds = [];
    private $resourceClasses = [];
    private $parentId = 0;
    private $page = 0;
    private $resultsPerPage = 1;
    private $contents = [];
    private $onlyTopLevel = false;

    /** @param ResourceKind[] $resourceKinds */
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

    public function setPage(int $page): ResourceListQueryBuilder {
        $this->page = $page;
        return $this;
    }

    public function setResultsPerPage(int $resultsPerPage): ResourceListQueryBuilder {
        $this->resultsPerPage = $resultsPerPage;
        return $this;
    }

    /** @param ResourceContents|array $contents */
    public function filterByContents($contents): ResourceListQueryBuilder {
        $this->contents = $contents;
        return $this;
    }

    public function onlyTopLevel(): ResourceListQueryBuilder {
        $this->onlyTopLevel = true;
        return $this;
    }

    public function build(): ResourceListQuery {
        return ResourceListQuery::withParams(
            $this->resourceClasses,
            $this->resourceKinds,
            $this->parentId,
            $this->contents instanceof ResourceContents ? $this->contents : ResourceContents::fromArray($this->contents),
            $this->onlyTopLevel,
            $this->page,
            $this->resultsPerPage
        );
    }
}
