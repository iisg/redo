<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\Command;

class MetadataUpdateCommand extends Command {
    private $metadataId;
    private $newLabel;
    private $newDescription;
    private $newPlaceholder;
    private $newConstraints;

    public function __construct(int $metadataId, array $newLabel, array $newDescription, array $newPlaceholder, array $newConstraints) {
        $this->metadataId = $metadataId;
        $this->newLabel = $newLabel;
        $this->newDescription = $newDescription;
        $this->newPlaceholder = $newPlaceholder;
        $this->newConstraints = $newConstraints;
    }

    public function getMetadataId(): int {
        return $this->metadataId;
    }

    public function getNewLabel(): array {
        return $this->newLabel;
    }

    public function getNewDescription(): array {
        return $this->newDescription;
    }

    public function getNewPlaceholder(): array {
        return $this->newPlaceholder;
    }

    public function getNewConstraints(): array {
        return $this->newConstraints;
    }

    public static function fromArray(int $metadataId, array $data): MetadataUpdateCommand {
        return new MetadataUpdateCommand(
            $metadataId,
            $data['label'] ?? [],
            $data['description'] ?? [],
            $data['placeholder'] ?? [],
            $data['constraints'] ?? []
        );
    }
}
