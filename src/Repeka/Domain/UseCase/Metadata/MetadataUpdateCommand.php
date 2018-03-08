<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\AdjustableCommand;

class MetadataUpdateCommand extends AbstractCommand implements AdjustableCommand {
    private $metadataId;
    private $newLabel;
    private $newDescription;
    private $newPlaceholder;
    private $newConstraints;
    private $newShownInBrief;
    private $newCopyToChildResource;

    public function __construct(
        int $metadataId,
        array $newLabel,
        array $newDescription,
        array $newPlaceholder,
        array $newConstraints,
        bool $newShownInBrief,
        bool $newCopyToChildResource
    ) {
        $this->metadataId = $metadataId;
        $this->newLabel = $newLabel;
        $this->newDescription = $newDescription;
        $this->newPlaceholder = $newPlaceholder;
        $this->newConstraints = $newConstraints;
        $this->newShownInBrief = $newShownInBrief;
        $this->newCopyToChildResource = $newCopyToChildResource;
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

    public function getNewShownInBrief(): bool {
        return $this->newShownInBrief;
    }

    public function getNewCopyToChildResource(): bool {
        return $this->newCopyToChildResource;
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
            $data['constraints'] ?? [],
            $data['shownInBrief'] ?? false,
            $data['copyToChildResource'] ?? false
        );
    }
}
