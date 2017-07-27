<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Cqrs\Command;

class ResourceKindUpdateCommand extends Command {
    private $label;
    private $metadataList;
    private $resourceKindId;

    public function __construct(int $resourceKindId, array $label, array $metadataList) {
        $this->resourceKindId = $resourceKindId;
        $this->label = $label;
        $this->metadataList = $metadataList;
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
}
