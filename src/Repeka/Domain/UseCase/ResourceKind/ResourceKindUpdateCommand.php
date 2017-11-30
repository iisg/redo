<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Cqrs\Command;

class ResourceKindUpdateCommand extends Command {
    private $label;
    private $metadataList;
    private $resourceKindId;
    private $displayStrategies;

    public function __construct(int $resourceKindId, array $label, array $metadataList, array $displayStrategies) {
        $this->resourceKindId = $resourceKindId;
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

    public function getResourceKindId(): int {
        return $this->resourceKindId;
    }

    public function getDisplayStrategies(): array {
        return $this->displayStrategies;
    }
}
