<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\AdjustableCommand;
use Repeka\Domain\Entity\ResourceKind;

class ResourceKindUpdateCommand extends AbstractCommand implements AdjustableCommand {
    private $label;
    private $metadataList;
    private $resourceKind;
    private $displayStrategies;

    public function __construct($resourceKindOrId, array $label, array $metadataList, array $displayStrategies) {
        $this->resourceKind = $resourceKindOrId;
        $this->label = $label;
        $this->metadataList = $metadataList;
        $this->displayStrategies = $displayStrategies;
    }

    public function getLabel(): array {
        return $this->label;
    }

    public function getMetadataList(): array {
        return $this->metadataList;
    }

    /** @return ResourceKind|int */
    public function getResourceKind() {
        return $this->resourceKind;
    }

    public function getDisplayStrategies(): array {
        return $this->displayStrategies;
    }
}
