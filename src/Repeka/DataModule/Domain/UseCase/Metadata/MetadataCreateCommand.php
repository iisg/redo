<?php
namespace Repeka\DataModule\Domain\UseCase\Metadata;

use Repeka\CoreModule\Domain\Cqrs\Command;

class MetadataCreateCommand extends Command {
    private $name;
    private $label;
    private $description;
    private $placeholder;
    private $control;

    public function __construct(string $name, array $label, array $description, array $placeholder, string $control) {
        $this->name = $name;
        $this->label = $label;
        $this->description = $description;
        $this->placeholder = $placeholder;
        $this->control = $control;
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

    public static function fromArray(array $data): MetadataCreateCommand {
        return new MetadataCreateCommand($data['name'], $data['label'], $data['description'], $data['placeholder'], $data['control']);
    }
}
