<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Cqrs\RequireOperatorRole;
use Repeka\Domain\UseCase\Audit\AbstractListQuery;

class ResourceKindListQuery extends AbstractListQuery {
    use RequireOperatorRole;

    /** @var int[] */
    private $ids;
    /** @var string[] */
    private $resourceClasses;
    private $metadataId;
    private $name;

    public static function builder(): ResourceKindListQueryBuilder {
        return new ResourceKindListQueryBuilder();
    }

    public static function withParams(
        array $ids,
        array $resourceClasses,
        int $metadataId,
        array $name,
        int $page,
        int $resultsPerPage
    ): ResourceKindListQuery {
        $query = new self($page, $resultsPerPage);
        $query->ids = $ids;
        $query->resourceClasses = $resourceClasses;
        $query->metadataId = $metadataId;
        $query->name = $name;
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
}
