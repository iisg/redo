<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\AdjustableCommand;
use Repeka\Domain\Cqrs\RequireOperatorRole;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\UseCase\Audit\AbstractListQuery;

/** @SuppressWarnings(PHPMD.ExcessiveParameterList) */
class ResourceListQuery extends AbstractListQuery implements AdjustableCommand {
    use RequireOperatorRole;

    private $ids;
    /** @var ResourceKind[] | int[] */
    private $resourceKinds;
    /** @var string[] */
    private $resourceClasses;
    /** @var int */
    private $parentId;
    /** @var bool */
    private $onlyTopLevel;
    /** @var ResourceContents */
    private $contentsFilter;
    /** @var array */
    private $sortBy;
    /** @var array */
    private $workflowPlacesIds;

    protected function __construct(int $page, int $resultsPerPage) {
        parent::__construct($page, $resultsPerPage);
    }

    public static function builder(): ResourceListQueryBuilder {
        return new ResourceListQueryBuilder();
    }

    public static function withParams(
        array $ids,
        array $resourceClasses,
        array $resourceKinds,
        array $sortBy,
        int $parentId,
        ResourceContents $contentsFilter,
        bool $onlyTopLevel,
        int $page,
        int $resultsPerPage,
        array $workflowPlacesIds
    ): ResourceListQuery {
        $query = new self($page, $resultsPerPage);
        $query->ids = $ids;
        $query->resourceKinds = $resourceKinds;
        $query->resourceClasses = $resourceClasses;
        $query->sortBy = $sortBy;
        $query->parentId = $parentId;
        $query->contentsFilter = $contentsFilter;
        $query->onlyTopLevel = $onlyTopLevel;
        $query->workflowPlacesIds = $workflowPlacesIds;
        return $query;
    }

    /** @return int[] */
    public function getIds(): array {
        return $this->ids;
    }

    /** @return string[] */
    public function getResourceClasses(): array {
        return $this->resourceClasses;
    }

    /** @return ResourceKind[] */
    public function getResourceKinds(): array {
        return $this->resourceKinds;
    }

    public function getParentId(): int {
        return $this->parentId;
    }

    public function getWorkflowPlacesIds(): array {
        return $this->workflowPlacesIds;
    }

    public function getSortBy(): array {
        return $this->sortBy;
    }

    public function onlyTopLevel(): bool {
        return $this->onlyTopLevel;
    }

    public function getContentsFilter(): ResourceContents {
        return $this->contentsFilter;
    }
}
