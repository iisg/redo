<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Cqrs\AdjustableCommand;
use Repeka\Domain\Cqrs\RequireOperatorRole;
use Repeka\Domain\UseCase\Audit\AbstractListQuery;

class ResourceKindListQuery extends AbstractListQuery implements AdjustableCommand {
    use RequireOperatorRole;

    /** @var int[] */
    private $ids;
    /** @var string[] */
    private $resourceClasses;
    private $metadataId;
    private $name;
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
        array $name,
        int $workflowId,
        int $page,
        int $resultsPerPage,
        array $sortBy
    ): ResourceKindListQuery {
        $query = new self($page, $resultsPerPage);
        $query->ids = $ids;
        $query->resourceClasses = $resourceClasses;
        $query->metadataId = $metadataId;
        $query->name = $name;
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

    public function getName(): array {
        return $this->name;
    }

    public function getWorkflowId(): int {
        return $this->workflowId;
    }

    public function getSortBy(): array {
        return $this->sortBy;
    }
}
