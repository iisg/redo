<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Cqrs\Command;

class ResourceKindCreateCommand extends Command {
    private $label;

    private $metadataList;

    public function __construct(array $label, array $metadataList) {
        $this->label = $label;
        $this->metadataList = $metadataList;
    }

    public function getLabel(): array {
        return $this->label;
    }

    public function getMetadataList(): array {
        return $this->metadataList;
    }

    public static function fromArray(array $data): ResourceKindCreateCommand {
        return new ResourceKindCreateCommand($data['label'], $data['metadataList']);
    }
}
