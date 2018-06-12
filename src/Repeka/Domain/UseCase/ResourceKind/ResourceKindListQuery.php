<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\RequireOperatorRole;

class ResourceKindListQuery extends AbstractCommand {
    use RequireOperatorRole;

    /** @var int[] */
    private $ids;
    /** @var string[] */
    private $resourceClasses;
    private $metadataId;

    private function __construct() {
    }

    public static function builder(): ResourceKindListQueryBuilder {
        return new ResourceKindListQueryBuilder();
    }

    public static function withParams(
        array $ids,
        array $resourceClasses,
        int $metadataId
    ): ResourceKindListQuery {
        $query = new self();
        $query->ids = $ids;
        $query->resourceClasses = $resourceClasses;
        $query->metadataId = $metadataId;
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
}
