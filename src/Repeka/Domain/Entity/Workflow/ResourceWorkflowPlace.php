<?php
namespace Repeka\Domain\Entity\Workflow;

use Cocur\Slugify\Slugify;
use Repeka\Domain\Entity\Identifiable;
use Repeka\Domain\Entity\Labeled;
use Repeka\Domain\Entity\ResourceEntity;

class ResourceWorkflowPlace implements Identifiable, Labeled {
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

    public function restrictingMetadataIds(): FluentRestrictingMetadataSelector {
        return new FluentRestrictingMetadataSelector($this->requiredMetadataIds, $this->lockedMetadataIds, $this->assigneeMetadataIds);
    }

    public function getMissingRequiredMetadataIds(ResourceEntity $resource): array {
        $requiredMetadataIds = $this->restrictingMetadataIds()->required()->assignees()->get();
        $presentMetadataIds = array_keys($resource->getContents()->toArray());
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
            'requiredMetadataIds' => $this->requiredMetadataIds,
            'lockedMetadataIds' => $this->lockedMetadataIds,
            'assigneeMetadataIds' => $this->assigneeMetadataIds,
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
