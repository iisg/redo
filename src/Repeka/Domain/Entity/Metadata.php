<?php
namespace Repeka\Domain\Entity;

use Assert\Assertion;

class Metadata {
    private $id;
    private $control;
    private $name;
    private $label = [];
    private $description = [];
    private $placeholder = [];
    /** @var Metadata */
    private $baseMetadata;
    private $resourceKind;
    private $ordinalNumber;
    /** @var  Metadata */
    private $parentMetadata;

    private function __construct() {
    }

    public static function create(string $control, string $name, array $label, array $placeholder = [], array $description = []): Metadata {
        $metadata = new self();
        $metadata->control = $control;
        $metadata->name = $name;
        $metadata->label = $label;
        $metadata->ordinalNumber = -1;
        $metadata->placeholder = $placeholder;
        $metadata->description = $description;
        return $metadata;
    }

    private static function createWithBase(Metadata $base) {
        $metadata = new self();
        $metadata->baseMetadata = $base;
        $metadata->ordinalNumber = -1;
        return $metadata;
    }

    public static function createForResourceKind(
        array $label,
        ResourceKind $resourceKind,
        Metadata $base,
        array $placeholder = [],
        array $description = []
    ) {
        $metadata = self::createWithBase($base);
        $metadata->label = $label;
        $metadata->resourceKind = $resourceKind;
        $metadata->placeholder = $placeholder;
        $metadata->description = $description;
        return $metadata;
    }

    public static function createChild(Metadata $base, Metadata $parent) {
        $metadata = self::createWithBase($base);
        $metadata->parentMetadata = $parent;
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

    public function isBase() {
        return !$this->baseMetadata;
    }

    public function getOrdinalNumber() {
        return $this->ordinalNumber;
    }

    public function isParent() {
        return !$this->parentMetadata;
    }

    public function getParentId() {
        return $this->isParent() ? null : $this->parentMetadata->getId();
    }

    public function setParent(Metadata $parent) {
        $this->parentMetadata = $parent;
    }

    public function getBaseId() {
        return $this->isBase() ? null : $this->baseMetadata->getId();
    }

    public function updateOrdinalNumber($newOrdinalNumber) {
        Assertion::greaterOrEqualThan($newOrdinalNumber, 0);
        $this->ordinalNumber = $newOrdinalNumber;
    }

    public function update(array $newLabel, array $newPlaceholder, array $newDescription) {
        $this->label = $this->isBase() ? array_merge($this->label, array_filter($newLabel, 'trim')) : array_filter($newLabel, 'trim');
        $this->placeholder = $newPlaceholder;
        $this->description = $newDescription;
    }

    public static function compareOrdinalNumbers(Metadata $a, Metadata $b): int {
        return $a->getOrdinalNumber() - $b->getOrdinalNumber();
    }
}
