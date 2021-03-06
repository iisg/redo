<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\User;
use Repeka\Domain\UseCase\Audit\AbstractListQueryBuilder;
use Repeka\Domain\Utils\ResourceListQuerySort;

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
    private $executor;

    public static function fromQuery(ResourceListQuery $query): ResourceListQueryBuilder {
        $builder = new self();
        $builder->ids = $query->getIds();
        $builder->resourceKinds = $query->getResourceKinds();
        $builder->resourceClasses = $query->getResourceClasses();
        $builder->parentId = $query->getParentId();
        $builder->onlyTopLevel = $query->onlyTopLevel();
        $builder->contentAlternatives = $query->getContentsFilters();
        $builder->sortBy = $query->getSortBy();
        $builder->workflowPlacesIds = $query->getWorkflowPlacesIds();
        $builder->permissionMetadataId = $query->getPermissionMetadataId();
        $builder->resultsPerPage = $query->getResultsPerPage();
        $builder->page = $query->getPage();
        return $builder;
    }

    /** @param ResourceKind[] | int[] | string[] $resourceKinds */
    public function filterByResourceKinds(array $resourceKinds): self {
        $this->resourceKinds = array_values(array_merge($this->resourceKinds, $resourceKinds));
        return $this;
    }

    /** @param ResourceKind|string|int $resourceKind */
    public function filterByResourceKind($resourceKind): self {
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

    /** @param array|ResourceListQuerySort[]|ResourceListQuerySort $sortBy */
    public function sortBy($sortBy): self {
        if (!is_array($sortBy)) {
            $sortBy = [$sortBy];
        }
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

    /** @param string[] $workflowPlacesIds */
    public function filterByWorkflowPlacesIds(array $workflowPlacesIds): self {
        $this->workflowPlacesIds = $workflowPlacesIds;
        return $this;
    }

    public function setPermissionMetadataId(int $metadataId): self {
        $this->permissionMetadataId = $metadataId;
        return $this;
    }

    public function setExecutor(User $user): self {
        $this->executor = $user;
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
            $this->permissionMetadataId,
            $this->executor
        );
    }
}
