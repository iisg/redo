<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\RequireNoRoles;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;

class MetadataListQuery extends AbstractCommand {
    use RequireNoRoles;

    private $systemMetadataIds;
    private $ids;
    private $names;
    private $parent;
    private $resourceClasses;
    private $onlyTopLevel;
    private $controls;
    private $requiredKindIds;
    private $excludedIds;

    private function __construct() {
    }

    public static function withParams(
        ?array $ids,
        ?array $names,
        ?array $resourceClasses,
        ?Metadata $parent,
        ?array $controls,
        ?bool $onlyTopLevel,
        ?array $systemMetadataIds,
        ?array $requiredKindIds,
        ?array $excludedIds
    ) {
        $query = new self();
        $query->ids = $ids ?: [];
        $query->names = $names ?: [];
        $query->resourceClasses = $resourceClasses ?: [];
        $query->parent = $parent;
        $query->controls = $controls ?: [];
        $query->onlyTopLevel = !!$onlyTopLevel;
        $query->systemMetadataIds = $systemMetadataIds ?: [];
        $query->requiredKindIds = $requiredKindIds ?: [];
        $query->excludedIds = $excludedIds ?: [];
        return $query;
    }

    public static function builder(): MetadataListQueryBuilder {
        return new MetadataListQueryBuilder();
    }

    /** @return int[] */
    public function getSystemMetadataIds(): array {
        return $this->systemMetadataIds;
    }

    /** @return int[] */
    public function getIds(): array {
        return $this->ids;
    }

    /** @return string[] */
    public function getNames(): array {
        return $this->names;
    }

    public function getParent(): ?Metadata {
        return $this->parent;
    }

    /** @return string[] */
    public function getResourceClasses(): array {
        return $this->resourceClasses;
    }

    public function onlyTopLevel(): bool {
        return $this->onlyTopLevel;
    }

    /** @return MetadataControl[] */
    public function getControls(): array {
        return $this->controls;
    }

    /** @return int[] */
    public function getRequiredKindIds(): array {
        return $this->requiredKindIds;
    }

    /** @return int[] */
    public function getExcludedIds(): array {
        return $this->excludedIds;
    }
}
