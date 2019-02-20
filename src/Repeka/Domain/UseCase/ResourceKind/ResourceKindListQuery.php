<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Cqrs\AdjustableCommand;
use Repeka\Domain\Cqrs\RequireNoRoles;
use Repeka\Domain\UseCase\Audit\AbstractListQuery;

class ResourceKindListQuery extends AbstractListQuery implements AdjustableCommand {
    use RequireNoRoles;

    /** @var int[] */
    private $ids;
    /** @var string[] */
    private $resourceClasses;
    private $metadataId;
    /** @var string[] */
    private $names;
    private $workflowId;
    /** @var array */
    private $sortBy;

    public static function builder(): ResourceKindListQueryBuilder {
        return new ResourceKindListQueryBuilder();
    }

    public static function withParams(
        array $ids,
        array $resourceClasses,
        int $metadataId,
        array $names,
        int $workflowId,
        int $page,
        int $resultsPerPage,
        array $sortBy
    ): ResourceKindListQuery {
        $query = new self($page, $resultsPerPage);
        $query->ids = $ids;
        $query->resourceClasses = $resourceClasses;
        $query->metadataId = $metadataId;
        $query->names = $names;
        $query->workflowId = $workflowId;
        $query->sortBy = $sortBy;
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

    public function getMetadataId(): int {
        return $this->metadataId;
    }

    /** @return string[] */
    public function getNames(): array {
        return $this->names;
    }

    public function getWorkflowId(): int {
        return $this->workflowId;
    }

    public function getSortBy(): array {
        return $this->sortBy;
    }
}
