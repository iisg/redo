<?php
namespace Repeka\Domain\Entity;

use Cocur\Slugify\Slugify;

class ResourceWorkflowPlace {
    private $id;
    private $label;
    private $requiredMetadataIds;
    private $lockedMetadataIds;

    public function __construct(array $label, $id = null, array $requiredMetadataIds = [], array $lockedMetadataIds = []) {
        $this->label = $label;
        $this->id = $id ?: (new Slugify())->slugify(current($label));
        $this->requiredMetadataIds = $requiredMetadataIds;
        $this->lockedMetadataIds = $lockedMetadataIds;
    }

    public function getId(): string {
        return $this->id;
    }

    public function getLabel(): array {
        return $this->label;
    }

    /** @return int[] */
    public function getRequiredMetadataIds(): array {
        return $this->requiredMetadataIds;
    }

    /** @return int[] */
    public function getLockedMetadataIds(): array {
        return $this->lockedMetadataIds;
    }

    public function getMissingRequiredMetadataIds(ResourceEntity $resource):array {
        $requiredMetadataIds = $this->getRequiredMetadataIds();
        $presentMetadataIds = array_keys($resource->getContents());
        $metadataIdsMissingForPlace = array_diff($requiredMetadataIds, $presentMetadataIds);
        return array_values($metadataIdsMissingForPlace);
    }

    public function resourceHasRequiredMetadata(ResourceEntity $resource): bool {
        $missingIds = $this->getMissingRequiredMetadataIds($resource);
        return count($missingIds) == 0;
    }

    public function toArray(): array {
        return [
            'id' => $this->getId(),
            'label' => $this->getLabel(),
            'requiredMetadataIds' => $this->getRequiredMetadataIds(),
            'lockedMetadataIds' => $this->getLockedMetadataIds(),
        ];
    }

    public static function fromArray(array $data) {
        return new self(
            $data['label'],
            $data['id'] ?? null,
            $data['requiredMetadataIds'] ?? [],
            $data['lockedMetadataIds'] ?? []
        );
    }
}
