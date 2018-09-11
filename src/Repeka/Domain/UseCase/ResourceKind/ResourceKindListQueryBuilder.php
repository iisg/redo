<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\UseCase\Audit\AbstractListQueryBuilder;

class ResourceKindListQueryBuilder extends AbstractListQueryBuilder {
    private $resourceClasses = [];
    private $ids = [];
    private $metadataId = 0;
    private $name = [];
    private $sortBy = [];

    public function filterByResourceClass(string $resourceClass): self {
        return $this->filterByResourceClasses([$resourceClass]);
    }

    public function filterByResourceClasses(array $resourceClasses): self {
        $this->resourceClasses = array_values(array_unique(array_merge($this->resourceClasses, $resourceClasses)));
        return $this;
    }

    public function filterByIds(array $ids): self {
        $this->ids = $ids;
        return $this;
    }

    public function filterByMetadataId(int $metadataId): self {
        $this->metadataId = $metadataId;
        return $this;
    }

    public function filterByName(array $name): self {
        $this->name = $name;
        return $this;
    }

    public function sortBy(array $sortBy): ResourceKindListQueryBuilder {
        $this->sortBy = array_replace($this->sortBy, $sortBy);
        return $this;
    }

    public function build(): ResourceKindListQuery {
        return ResourceKindListQuery::withParams(
            $this->ids,
            $this->resourceClasses,
            $this->metadataId,
            $this->name,
            $this->page,
            $this->resultsPerPage,
            $this->sortBy
        );
    }
}
