<?php
namespace Repeka\Domain\Entity;

use Assert\Assertion;

class Metadata implements Identifiable {
    /**
     * This array key is used to store names of constraints that were present in base metadata, but were removed in overriding metadata.
     */
    const REMOVED_CONSTRAINTS_KEY = '__removedConstraints';

    private $id;
    /** @var string */
    private $control;
    private $name;
    private $label = [];
    private $description = [];
    private $placeholder = [];
    /** @var Metadata */
    private $baseMetadata;
    private $resourceKind;
    private $ordinalNumber;
    /** @var Metadata */
    private $parentMetadata;
    private $constraints = [];
    private $shownInBrief;
    private $resourceClass;

    private function __construct() {
    }

    /** @SuppressWarnings("PHPMD.BooleanArgumentFlag") */
    public static function create(
        string $resourceClass,
        MetadataControl $control,
        string $name,
        array $label,
        array $placeholder = [],
        array $description = [],
        array $constraints = [],
        bool $shownInBrief = false
    ): Metadata {
        Assertion::allNotNull($constraints);
        $metadata = new self();
        $metadata->resourceClass = $resourceClass;
        $metadata->control = $control->getValue();  // $control must be a string internally because it's so when read from DB
        $metadata->name = $name;
        $metadata->label = $label;
        $metadata->ordinalNumber = -1;
        $metadata->placeholder = $placeholder;
        $metadata->description = $description;
        $metadata->constraints = $constraints;
        $metadata->shownInBrief = $shownInBrief;
        return $metadata;
    }

    private static function createWithBase(Metadata $base): Metadata {
        $metadata = new self();
        $metadata->baseMetadata = $base;
        $metadata->ordinalNumber = -1;
        $metadata->resourceClass = $base->resourceClass;
        return $metadata;
    }

    /** @SuppressWarnings(PHPMD.BooleanArgumentFlag) */
    public static function createForResourceKind(
        array $label,
        ResourceKind $resourceKind,
        Metadata $base,
        array $placeholder = [],
        array $description = [],
        array $constraints = [],
        bool $shownInBrief = null
    ): Metadata {
        Assertion::allNotNull($constraints);
        $metadata = self::createWithBase($base);
        $metadata->label = $label;
        $metadata->resourceKind = $resourceKind;
        $metadata->placeholder = $placeholder;
        $metadata->description = $description;
        $metadata->constraints = $constraints;
        $metadata->shownInBrief = $shownInBrief;
        return $metadata;
    }

    public static function createChild(Metadata $base, Metadata $parent): Metadata {
        $metadata = self::createWithBase($base);
        $metadata->parentMetadata = $parent;
        return $metadata;
    }

    public function getId() {
        return $this->id;
    }

    public function getControl(): MetadataControl {
        return $this->baseMetadata ? $this->baseMetadata->getControl() : new MetadataControl($this->control);
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

    public function getConstraints(): array {
        if (!$this->baseMetadata) {
            return $this->constraints;
        } else {
            $patchedConstraints = $this->baseMetadata->constraints;
            foreach ($this->constraints as $name => $value) {
                if ($name == self::REMOVED_CONSTRAINTS_KEY) {
                    continue;  // key for internal use, not an override, so skip it
                }
                $patchedConstraints[$name] = $value;
            }
            foreach ($this->constraints[self::REMOVED_CONSTRAINTS_KEY] ?? [] as $removedConstraint) {
                if (array_key_exists($removedConstraint, $patchedConstraints)) {
                    unset($patchedConstraints[$removedConstraint]);
                }
            }
            return $patchedConstraints;
        }
    }

    public function getResourceClass(): ?string {
        return $this->baseMetadata ? $this->baseMetadata->getResourceClass() : $this->resourceClass;
    }

    public function isShownInBrief(): bool {
        return ($this->isBase())
            ? $this->shownInBrief
            : $this->shownInBrief ?? $this->baseMetadata->isShownInBrief();
    }

    public function getBaseId(): ?int {
        return $this->isBase() ? null : $this->baseMetadata->getId();
    }

    public function updateOrdinalNumber($newOrdinalNumber) {
        Assertion::greaterOrEqualThan($newOrdinalNumber, 0);
        $this->ordinalNumber = $newOrdinalNumber;
    }

    private function removeCopiedFromBase(array $array, array $baseArray): array {
        $filtered = $array;
        foreach ($baseArray as $key => $baseValue) {
            if (array_key_exists($key, $array) && $array[$key] == $baseValue) {
                // key unchanged, it will be inherited from base metadata - don't store a copy
                unset($filtered[$key]);
            }
        }
        return $filtered;
    }

    public function update(
        array $newLabel,
        array $newPlaceholder,
        array $newDescription,
        array $newConstraints,
        bool $shownInBrief
    ) {
        Assertion::allNotNull($newConstraints);
        if ($this->isBase()) {
            $this->label = array_filter($newLabel, 'trim');
            $this->placeholder = $newPlaceholder;
            $this->description = $newDescription;
            $this->constraints = $newConstraints;
            $this->shownInBrief = $shownInBrief;
        } else {
            $this->label = $this->removeCopiedFromBase($newLabel, $this->baseMetadata->getLabel());
            $this->placeholder = $this->removeCopiedFromBase($newPlaceholder, $this->baseMetadata->getPlaceholder());
            $this->description = $this->removeCopiedFromBase($newDescription, $this->baseMetadata->getDescription());
            $this->constraints = $this->getBaseConstraintsPatch($newConstraints, $this->baseMetadata->getConstraints());
            $this->shownInBrief = ($shownInBrief == $this->baseMetadata->shownInBrief) ? null : $shownInBrief;
        }
    }

    private function getBaseConstraintsPatch(array $constraints, array $baseConstraints): array {
        $patch = [];
        foreach ($constraints as $name => &$value) {  // mind that $value is a reference
            $valueIsInherited = array_key_exists($name, $baseConstraints) && $baseConstraints[$name] == $value;
            if (!$valueIsInherited) {
                $patch[$name] = $value;
            }
        }
        $removedKeys = array_diff(array_keys($baseConstraints), array_keys($constraints));
        if (!empty($removedKeys)) {
            $patch[self::REMOVED_CONSTRAINTS_KEY] = $removedKeys;
        }
        return $patch;
    }

    public static function compareOrdinalNumbers(Metadata $a, Metadata $b): int {
        return $a->getOrdinalNumber() - $b->getOrdinalNumber();
    }
}
