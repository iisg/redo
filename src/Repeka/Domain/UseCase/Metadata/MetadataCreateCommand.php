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

    public function __construct(
        string $name,
        array $label,
        array $description,
        array $placeholder,
        string $control,
        array $constraints = []
    ) {
        $this->name = $name;
        $this->label = $label;
        $this->description = $description;
        $this->placeholder = $placeholder;
        $this->control = $control;
        $this->constraints = $constraints;
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

    public static function fromArray(array $data): MetadataCreateCommand {
        return new MetadataCreateCommand(
            $data['name'] ?? '',
            $data['label'] ?? [],
            $data['description'] ?? [],
            $data['placeholder'] ?? [],
            $data['control'] ?? 'text',
            $data['constraints'] ?? []
        );
    }
}
