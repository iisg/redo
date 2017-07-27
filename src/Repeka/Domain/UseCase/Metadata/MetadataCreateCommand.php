<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\Command;

class MetadataCreateCommand extends Command {
    private $name;
    private $label;
    private $description;
    private $placeholder;
    private $control;
    private $constraints;
    private $shownInBrief;
    private $resourceClass;

    /** @SuppressWarnings("PHPMD.BooleanArgumentFlag") */
    public function __construct(
        string $name,
        array $label,
        array $description,
        array $placeholder,
        string $control,
        string $resourceClass,
        array $constraints = [],
        bool $shownInBrief = false
    ) {
        $this->name = $name;
        $this->label = $label;
        $this->description = $description;
        $this->placeholder = $placeholder;
        $this->control = $control;
        $this->constraints = $constraints;
        $this->shownInBrief = $shownInBrief;
        $this->resourceClass = $resourceClass;
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

    public function getControl(): string {
        return $this->control;
    }

    public function getConstraints(): array {
        return $this->constraints;
    }

    public function getResourceClass(): string {
        return $this->resourceClass;
    }

    public function isShownInBrief(): ?bool {
        return $this->shownInBrief;
    }

    public static function fromArray(array $data): MetadataCreateCommand {
        return new MetadataCreateCommand(
            $data['name'] ?? '',
            $data['label'] ?? [],
            $data['description'] ?? [],
            $data['placeholder'] ?? [],
            $data['control'] ?? 'text',
            $data['resourceClass'] ?? '',
            $data['constraints'] ?? [],
            $data['shownInBrief'] ?? false
        );
    }
}
