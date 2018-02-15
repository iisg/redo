<?php
namespace Repeka\Domain\Entity;

use Assert\Assertion;

class Metadata implements Identifiable {
    private $id;
    /** @var string */
    private $control;
    private $name;
    private $label = [];
    private $description = [];
    private $placeholder = [];
    /** @var Metadata */
    private $baseMetadata;
    private $ordinalNumber;
    /** @var Metadata */
    private $parentMetadata;
    private $constraints = [];
    private $shownInBrief;
    private $resourceClass;
    private $overrides = [];

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

    public static function createChild(Metadata $base, Metadata $parent): Metadata {
        $metadata = new self();
        $metadata->baseMetadata = $base;
        $metadata->ordinalNumber = -1;
        $metadata->resourceClass = $base->resourceClass;
        $metadata->control = $base->control;
        $metadata->parentMetadata = $parent;
        return $metadata;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getControl(): MetadataControl {
        return new MetadataControl($this->control);
    }

    public function getName(): string {
        return $this->isBase() ? $this->name : $this->baseMetadata->getName();
    }

    public function getResourceClass(): string {
        return $this->resourceClass;
    }

    public function getLabel(): array {
        return array_merge($this->label, array_filter($this->overrides['label'] ?? [], 'trim'));
    }

    public function getDescription(): array {
        return array_merge($this->description, array_filter($this->overrides['description'] ?? [], 'trim'));
    }

    public function getPlaceholder(): array {
        return array_merge($this->placeholder, array_filter($this->overrides['placeholder'] ?? [], 'trim'));
    }

    public function isBase(): bool {
        return !$this->baseMetadata;
    }

    public function isParent(): bool {
        return !$this->parentMetadata;
    }

    public function getParentId() {
        return $this->isParent() ? null : $this->parentMetadata->getId();
    }

    public function setParent(Metadata $parent) {
        $this->parentMetadata = $parent;
    }

    public function getConstraints(): array {
        return array_merge($this->constraints, $this->overrides['constraints'] ?? []);
    }

    public function isShownInBrief(): bool {
        return is_bool($this->overrides['shownInBrief'] ?? null) ? $this->overrides['shownInBrief'] : $this->shownInBrief;
    }

    public function getBaseId(): ?int {
        return $this->isBase() ? null : $this->baseMetadata->getId();
    }

    public function updateOrdinalNumber($newOrdinalNumber) {
        Assertion::greaterOrEqualThan($newOrdinalNumber, 0);
        $this->ordinalNumber = $newOrdinalNumber;
    }

    public function update(array $newLabel, array $newPlaceholder, array $newDescription, array $newConstraints, bool $shownInBrief) {
        Assertion::allNotNull($newConstraints);
        $this->label = array_filter($newLabel, 'trim');
        $this->placeholder = $newPlaceholder;
        $this->description = $newDescription;
        $this->constraints = $newConstraints;
        $this->shownInBrief = $shownInBrief;
    }

    public function updateOverrides(array $overrides) {
        $this->overrides = [
            'label' => $this->removeValuesOverridingToTheSameThing($overrides['label'] ?? [], $this->label),
            'description' => $this->removeValuesOverridingToTheSameThing($overrides['description'] ?? [], $this->description),
            'placeholder' => $this->removeValuesOverridingToTheSameThing($overrides['placeholder'] ?? [], $this->placeholder),
            'constraints' => $this->removeValuesOverridingToTheSameThing($overrides['constraints'] ?? [], $this->constraints),
            'shownInBrief' => isset($overrides['shownInBrief']) && is_bool($overrides['shownInBrief']) ? $overrides['shownInBrief'] : null,
        ];
    }

    private function removeValuesOverridingToTheSameThing(array $overrides, array $actualValues): array {
        $filtered = $overrides;
        foreach ($actualValues as $key => $actualValue) {
            if (array_key_exists($key, $overrides) && $overrides[$key] == $actualValue) {
                unset($filtered[$key]);
            }
        }
        return $filtered;
    }

    public function getOverrides(): array {
        $overrides = $this->overrides;
        if (isset($overrides['shownInBrief'])) {
            if ($this->shownInBrief === $overrides['shownInBrief'] || !is_bool($overrides['shownInBrief'])) {
                unset($overrides['shownInBrief']);
            }
        }
        return $overrides;
    }

    public function setOverrides($overrides) {
        $this->overrides = $overrides;
    }
}
