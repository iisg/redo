<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\AdjustableCommand;
use Repeka\Domain\Cqrs\ResourceClassAwareCommand;
use Repeka\Domain\Entity\Metadata;

class MetadataUpdateCommand extends ResourceClassAwareCommand implements AdjustableCommand {
    private $metadata;
    private $newLabel;
    private $newDescription;
    private $newPlaceholder;
    private $newConstraints;
    private $newGroupId;
    private $newDisplayStrategy;
    private $newShownInBrief;
    private $newCopyToChildResource;

    public function __construct(
        $metadataOrId,
        array $newLabel,
        array $newDescription,
        array $newPlaceholder,
        array $newConstraints,
        string $newGroupId,
        ?string $newDisplayStrategy,
        bool $newShownInBrief,
        bool $newCopyToChildResource
    ) {
        parent::__construct($metadataOrId);
        $this->metadata = $metadataOrId;
        $this->newLabel = $newLabel;
        $this->newDescription = $newDescription;
        $this->newPlaceholder = $newPlaceholder;
        $this->newConstraints = $newConstraints;
        $this->newGroupId = $newGroupId;
        $this->newDisplayStrategy = $newDisplayStrategy;
        $this->newShownInBrief = $newShownInBrief;
        $this->newCopyToChildResource = $newCopyToChildResource;
    }

    /** @return Metadata */
    public function getMetadata() {
        return $this->metadata;
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

    public function getNewGroupId(): string {
        return $this->newGroupId;
    }

    public function getNewDisplayStrategy(): ?string {
        return $this->newDisplayStrategy;
    }

    /**
     * @param Metadata|int $metadataOrId
     * @param array $data
     * @return self
     */
    public static function fromArray($metadataOrId, array $data): self {
        return new MetadataUpdateCommand(
            $metadataOrId,
            $data['label'] ?? ($metadataOrId instanceof Metadata ? $metadataOrId->getLabel() : []),
            $data['description'] ?? ($metadataOrId instanceof Metadata ? $metadataOrId->getDescription() : []),
            $data['placeholder'] ?? ($metadataOrId instanceof Metadata ? $metadataOrId->getPlaceholder() : []),
            $data['constraints'] ?? ($metadataOrId instanceof Metadata ? $metadataOrId->getConstraints() : []),
            $data['groupId'] ?? ($metadataOrId instanceof Metadata ? $metadataOrId->getGroupId() : Metadata::DEFAULT_GROUP),
            $data['displayStrategy'] ?? null,
            $data['shownInBrief'] ?? ($metadataOrId instanceof Metadata ? $metadataOrId->isShownInBrief() : false),
            $data['copyToChildResource'] ?? ($metadataOrId instanceof Metadata ? $metadataOrId->isCopiedToChildResource() : false)
        );
    }
}
