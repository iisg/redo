<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\UseCase\Audit\AbstractListQueryBuilder;

/** @SuppressWarnings(PHPMD.TooManyPublicMethods) */
class ResourceListQueryBuilder extends AbstractListQueryBuilder {
    private $resourceKinds = [];
    private $resourceClasses = [];
    private $sortBy = [];
    private $parentId = 0;
    private $contentAlternatives = [];
    private $onlyTopLevel = false;
    private $ids = [];
    private $workflowPlacesIds = [];
    private $permissionMetadataId = SystemMetadata::VISIBILITY;

    /** @param ResourceKind[] | int[] $resourceKinds */
    public function filterByResourceKinds(array $resourceKinds): self {
        $this->resourceKinds = array_values(array_merge($this->resourceKinds, $resourceKinds));
        return $this;
    }

    public function filterByResourceKind(ResourceKind $resourceKind): self {
        return $this->filterByResourceKinds([$resourceKind]);
    }

    public function filterByResourceClasses(array $resourceClasses): self {
        $this->resourceClasses = array_values(array_unique(array_merge($this->resourceClasses, $resourceClasses)));
        return $this;
    }

    public function filterByResourceClass(string $resourceClass): self {
        return $this->filterByResourceClasses([$resourceClass]);
    }

    public function filterByParentId(int $parentId): self {
        $this->parentId = $parentId;
        return $this;
    }

    public function sortBy(array $sortBy): self {
        $this->sortBy = array_replace($this->sortBy, $sortBy);
        return $this;
    }

    /**
     * Each call to filterByContents adds an alternative to query.
     * @param ResourceContents|array $contents
     */
    public function filterByContents($contents): self {
        $this->contentAlternatives[] = $contents instanceof ResourceContents ? $contents : ResourceContents::fromArray($contents);
        return $this;
    }

    public function filterByIds(array $ids): self {
        $this->ids = $ids;
        return $this;
    }

    public function onlyTopLevel(): self {
        $this->onlyTopLevel = true;
        return $this;
    }

    public function filterByWorkflowPlacesIds(array $workflowPlacesIds): self {
        $this->workflowPlacesIds = $workflowPlacesIds;
        return $this;
    }

    public function setPermissionMetadataId(int $metadataId): self {
        $this->permissionMetadataId = $metadataId;
        return $this;
    }

    public function build(): ResourceListQuery {
        return ResourceListQuery::withParams(
            $this->ids,
            $this->resourceClasses,
            $this->resourceKinds,
            $this->sortBy,
            $this->parentId,
            $this->contentAlternatives,
            $this->onlyTopLevel,
            $this->page,
            $this->resultsPerPage,
            $this->workflowPlacesIds,
            $this->permissionMetadataId
        );
    }
}
