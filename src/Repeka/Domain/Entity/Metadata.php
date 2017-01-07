<?php
namespace Repeka\Domain\Entity;

use Assert\Assertion;

class Metadata {
    private $id;
    private $control;
    private $name;
    private $label;
    private $description;
    private $placeholder;
    /** @var Metadata */
    private $baseMetadata;
    private $resourceKind;
    private $ordinalNumber;

    private function __construct() {
    }

    public static function create(string $control, string $name, array $label): Metadata {
        $metadata = new self();
        $metadata->control = $control;
        $metadata->name = $name;
        $metadata->label = $label;
        $metadata->ordinalNumber = -1;
        return $metadata;
    }

    public static function createForResourceKind(array $label, ResourceKind $resourceKind, Metadata $base) {
        $metadata = new self();
        $metadata->label = $label;
        $metadata->baseMetadata = $base;
        $metadata->resourceKind = $resourceKind;
        $metadata->ordinalNumber = -1;
        return $metadata;
    }

    public function getId() {
        return $this->id;
    }

    public function getControl(): string {
        return $this->baseMetadata ? $this->baseMetadata->getControl() : $this->control;
    }

    public function getName(): string {
        return $this->baseMetadata ? $this->baseMetadata->getName() : $this->name;
    }

    public function getLabel(): array {
        return array_merge($this->baseMetadata ? $this->baseMetadata->getLabel() : [], array_filter($this->label, 'trim'));
    }

    public function getDescription(): array {
        return array_merge($this->baseMetadata ? $this->baseMetadata->getDescription() : [], array_filter($this->description, 'trim'));
    }

    public function getPlaceholder(): array {
        return array_merge($this->baseMetadata ? $this->baseMetadata->getPlaceholder() : [], array_filter($this->placeholder, 'trim'));
    }

    public function getResourceKind(): ResourceKind {
        return $this->resourceKind;
    }

    public function setDescription(array $description): Metadata {
        $this->description = $description;
        return $this;
    }

    public function setPlaceholder(array $placeholder): Metadata {
        $this->placeholder = $placeholder;
        return $this;
    }

    public function isBase() {
        return !$this->baseMetadata;
    }

    public function getOrdinalNumber() {
        return $this->ordinalNumber;
    }

    public function updateOrdinalNumber($newOrdinalNumber) {
        Assertion::greaterOrEqualThan($newOrdinalNumber, 0);
        $this->ordinalNumber = $newOrdinalNumber;
    }
}
