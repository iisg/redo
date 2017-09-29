<?php
namespace Repeka\Domain\Entity;

use Cocur\Slugify\Slugify;

class ResourceWorkflowPlace {
    private $id;
    private $label;
    private $requiredMetadataIds;
    private $lockedMetadataIds;
    private $assigneeMetadataIds;

    public function __construct(
        array $label,
        $id = null,
        array $requiredMetadataIds = [],
        array $lockedMetadataIds = [],
        array $assigneeMetadataIds = []
    ) {
        $this->label = $label;
        $this->id = $id ?: (new Slugify())->slugify(current($label));
        $this->requiredMetadataIds = $requiredMetadataIds;
        $this->lockedMetadataIds = $lockedMetadataIds;
        $this->assigneeMetadataIds = $assigneeMetadataIds;
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

    /**
     * @return int[]
     * @SuppressWarnings("PHPMD.BooleanArgumentFlag")
     */
    public function getLockedMetadataIds(): array {
        return $this->lockedMetadataIds;
    }

    /**
     * @return int[]
     * @SuppressWarnings("PHPMD.BooleanArgumentFlag")
     */
    public function getAssigneeMetadataIds(): array {
        return $this->assigneeMetadataIds;
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
            'assigneeMetadataIds' => $this->getAssigneeMetadataIds(),
        ];
    }

    public static function fromArray(array $data) {
        return new self(
            $data['label'] ?? [],
            $data['id'] ?? null,
            $data['requiredMetadataIds'] ?? [],
            $data['lockedMetadataIds'] ?? [],
            $data['assigneeMetadataIds'] ?? []
        );
    }
}
