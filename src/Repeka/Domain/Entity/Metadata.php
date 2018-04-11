<?php
namespace Repeka\Domain\Entity;

use Assert\Assertion;
use Repeka\Domain\Constants\SystemResourceClass;
use Repeka\Domain\Repository\ResourceKindRepository;

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
    private $shownInBrief = false;
    private $copyToChildResource = false;
    private $resourceClass;
    private $overrides = [];

    private function __construct() {
    }

    /** @SuppressWarnings("PHPMD.BooleanArgumentFlag")
     * @param string $resourceClass
     * @param MetadataControl $control
     * @param string $name
     * @param array $label
     * @param array $placeholder
     * @param array $description
     * @param array $constraints
     * @param bool $shownInBrief
     * @param bool $copyToChildResource
     * @return Metadata
     */
    public static function create(
        string $resourceClass,
        MetadataControl $control,
        string $name,
        array $label,
        array $placeholder = [],
        array $description = [],
        array $constraints = [],
        bool $shownInBrief = false,
        bool $copyToChildResource = false
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
        $metadata->copyToChildResource = $copyToChildResource;
        return $metadata;
    }

    public static function createChild(Metadata $base, Metadata $parent): Metadata {
        $metadata = new self();
        $metadata->baseMetadata = $base;
        $metadata->ordinalNumber = -1;
        $metadata->resourceClass = $base->resourceClass;
        $metadata->control = $base->control;
        $metadata->parentMetadata = $parent;
        $metadata->shownInBrief = $base->shownInBrief;
        $metadata->copyToChildResource = $base->copyToChildResource;
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

    public function isTopLevel(): bool {
        return !$this->parentMetadata;
    }

    public function getParentId() {
        return $this->isTopLevel() ? null : $this->parentMetadata->getId();
    }

    public function getParent(): Metadata {
        Assertion::false($this->isTopLevel());
        return $this->parentMetadata;
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

    public function isCopiedToChildResource(): bool {
        return is_bool($this->overrides['copyToChildResource'] ?? null)
            ? $this->overrides['copyToChildResource']
            : $this->copyToChildResource;
    }

    public function getBaseId(): ?int {
        return $this->isBase() ? null : $this->baseMetadata->getId();
    }

    public function updateOrdinalNumber($newOrdinalNumber) {
        Assertion::greaterOrEqualThan($newOrdinalNumber, 0);
        $this->ordinalNumber = $newOrdinalNumber;
    }

    public function update(
        array $newLabel,
        array $newPlaceholder,
        array $newDescription,
        array $newConstraints,
        bool $shownInBrief,
        bool $copyToChildResource
    ) {
        Assertion::allNotNull($newConstraints);
        $this->label = array_filter($newLabel, 'trim');
        $this->placeholder = $newPlaceholder;
        $this->description = $newDescription;
        $this->constraints = $newConstraints;
        $this->shownInBrief = $shownInBrief;
        $this->copyToChildResource = $copyToChildResource;
    }

    public function withOverrides(array $overrides): Metadata {
        $overrides = [
            'label' => $this->removeValuesOverridingToTheSameThing($overrides['label'] ?? [], $this->label),
            'description' => $this->removeValuesOverridingToTheSameThing($overrides['description'] ?? [], $this->description),
            'placeholder' => $this->removeValuesOverridingToTheSameThing($overrides['placeholder'] ?? [], $this->placeholder),
            'constraints' => $this->removeValuesOverridingToTheSameThing($overrides['constraints'] ?? [], $this->constraints),
            'shownInBrief' => $this->isSetOverride($overrides, 'shownInBrief'),
            'copyToChildResource' => $this->isSetOverride($overrides, 'copyToChildResource'),
        ];
        $overrides = array_filter(
            $overrides,
            function ($override) {
                return !is_null($override) && !is_array($override) || !empty($override);
            }
        );
        $metadata = clone $this;
        $metadata->overrides = $overrides;
        return $metadata;
    }

    private function isSetOverride(array $overrides, string $overrideKey): ?bool {
        return isset($overrides[$overrideKey]) && is_bool($overrides[$overrideKey]) ? $overrides[$overrideKey] : null;
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
        $overrides = $this->getSingleOverride($overrides, $this->shownInBrief, 'shownInBrief');
        $overrides = $this->getSingleOverride($overrides, $this->copyToChildResource, 'copyToChildResource');
        return $overrides;
    }

    private function getSingleOverride(array $overrides, bool $overrideValue, string $overrideKey): array {
        if (isset($overrides[$overrideKey])) {
            if ($overrideValue === $overrides[$overrideKey] || !is_bool($overrides[$overrideKey])) {
                unset($overrides[$overrideKey]);
            }
        }
        return $overrides;
    }

    public function canDetermineAssignees(ResourceKindRepository $resourceKindRepository): bool {
        if ($this->control == MetadataControl::RELATIONSHIP) {
            $allowedResourceKindIds = $this->getConstraints()['resourceKind'] ?? [];
            if ($allowedResourceKindIds) {
                $resourceClasses = array_map(
                    function (int $resourceKindId) use ($resourceKindRepository) {
                        return $resourceKindRepository->findOne($resourceKindId)->getResourceClass();
                    },
                    $allowedResourceKindIds
                );
                return array_unique($resourceClasses) == [SystemResourceClass::USER];
            }
        }
        return false;
    }
}
