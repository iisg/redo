<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\AdjustableCommand;
use Repeka\Domain\Cqrs\ResourceClassAwareCommand;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;

/** @SuppressWarnings(PHPMD.ExcessiveParameterList) */
class MetadataCreateCommand extends ResourceClassAwareCommand implements AdjustableCommand {
    private $name;
    private $label;
    private $description;
    private $placeholder;
    private $controlName;
    private $constraints;
    private $groupId;
    private $displayStrategy;
    private $shownInBrief;
    private $copyToChildResource;
    private $parent;

    /** @SuppressWarnings("PHPMD.BooleanArgumentFlag") */
    public function __construct(
        string $name,
        array $label,
        array $description,
        array $placeholder,
        string $controlName,
        string $resourceClass,
        array $constraints = [],
        string $groupId = Metadata::DEFAULT_GROUP,
        ?string $displayStrategy = null,
        bool $shownInBrief = false,
        bool $copyToChildResource = false,
        ?Metadata $parent = null
    ) {
        parent::__construct($resourceClass ?: ($parent ? $parent->getResourceClass() : ''));
        $this->name = $name;
        $this->label = $label;
        $this->description = $description;
        $this->placeholder = $placeholder;
        $this->controlName = $controlName;
        $this->constraints = $constraints;
        $this->groupId = $groupId;
        $this->displayStrategy = $displayStrategy;
        $this->shownInBrief = $shownInBrief;
        $this->copyToChildResource = $copyToChildResource;
        $this->parent = $parent;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getLabel(): array {
        return $this->label;
    }

    public function getDescription(): array {
        return $this->description;
    }

    public function getPlaceholder(): array {
        return $this->placeholder;
    }

    public function getControlName(): string {
        return $this->controlName;
    }

    public function getConstraints(): array {
        return $this->constraints;
    }

    public function getGroupId(): string {
        return $this->groupId;
    }

    public function getDisplayStrategy(): ?string {
        return $this->displayStrategy;
    }

    public function isShownInBrief(): bool {
        return $this->shownInBrief;
    }

    public function isCopiedToChildResource(): bool {
        return $this->copyToChildResource;
    }

    public function getParent(): ?Metadata {
        return $this->parent;
    }

    public static function fromArray(array $data): MetadataCreateCommand {
        return new MetadataCreateCommand(
            $data['name'] ?? '',
            $data['label'] ?? [],
            $data['description'] ?? [],
            $data['placeholder'] ?? [],
            $data['control'] ?? MetadataControl::TEXT,
            $data['resourceClass'] ?? '',
            $data['constraints'] ?? [],
            $data['groupId'] ?? Metadata::DEFAULT_GROUP,
            $data['displayStrategy'] ?? null ?: null,
            $data['shownInBrief'] ?? false,
            $data['copyToChildResource'] ?? false,
            $data['parent'] ?? null
        );
    }
}
