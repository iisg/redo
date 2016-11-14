<?php
namespace Repeka\DataModule\Domain\Entity;

class Metadata {
    private $id;

    private $control;

    private $name;

    private $label;

    private $description;

    private $placeholder;

    public function __construct(string $control, string $name, array $label) {
        $this->control = $control;
        $this->name = $name;
        $this->label = $label;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getControl(): string {
        return $this->control;
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

    public function setName(string $name) {
        $this->name = $name;
    }

    public function setLabel(array $label): Metadata {
        $this->label = $label;
        return $this;
    }

    public function setDescription(array $description): Metadata {
        $this->description = $description;
        return $this;
    }

    public function setPlaceholder(array $placeholder): Metadata {
        $this->placeholder = $placeholder;
        return $this;
    }
}
