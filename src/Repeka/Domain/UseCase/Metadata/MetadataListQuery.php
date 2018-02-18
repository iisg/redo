<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;

class MetadataListQuery extends AbstractCommand {
    private $parent;
    private $resourceClasses;
    private $onlyTopLevel;
    private $controls;

    private function __construct() {
    }

    public static function withParams(
        ?array $resourceClasses,
        ?Metadata $parent,
        ?array $controls,
        ?bool $onlyTopLevel
    ) {
        $query = new self();
        $query->resourceClasses = $resourceClasses ?: [];
        $query->parent = $parent;
        $query->controls = $controls ?: [];
        $query->onlyTopLevel = !!$onlyTopLevel;
        return $query;
    }

    public static function builder(): MetadataListQueryBuilder {
        return new MetadataListQueryBuilder();
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
}
