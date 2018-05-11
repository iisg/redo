<?php
namespace Repeka\Domain\UseCase\ResourceKind;

class ResourceKindListQueryBuilder {
    private $resourceClasses = [];
    private $ids = [];
    private $metadataId = 0;

    public function filterByResourceClass(string $resourceClass): ResourceKindListQueryBuilder {
        return $this->filterByResourceClasses([$resourceClass]);
    }

    public function filterByResourceClasses(array $resourceClasses): ResourceKindListQueryBuilder {
        $this->resourceClasses = array_values(array_unique(array_merge($this->resourceClasses, $resourceClasses)));
        return $this;
    }

    public function filterByIds(array $ids): ResourceKindListQueryBuilder {
        $this->ids = $ids;
        return $this;
    }

    public function filterByMetadataId(int $metadataId): ResourceKindListQueryBuilder {
        $this->metadataId = $metadataId;
        return $this;
    }

    public function build(): ResourceKindListQuery {
        return ResourceKindListQuery::withParams(
            $this->ids,
            $this->resourceClasses,
            $this->metadataId
        );
    }
}
