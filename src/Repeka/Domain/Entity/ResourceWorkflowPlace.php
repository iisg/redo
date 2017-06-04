<?php
namespace Repeka\Domain\Entity;

use Cocur\Slugify\Slugify;

class ResourceWorkflowPlace {
    private $id;
    private $label;
    private $requiredMetadataIds;

    public function __construct(array $label, $id = null, array $requiredMetadataIds = []) {
        $this->label = $label;
        $this->id = $id ?: (new Slugify())->slugify(current($label));
        $this->requiredMetadataIds = $requiredMetadataIds;
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

    public function toArray(): array {
        return [
            'id' => $this->getId(),
            'label' => $this->getLabel(),
            'requiredMetadataIds' => $this->getRequiredMetadataIds(),
        ];
    }

    public function isRequiredMetadataFilled(ResourceEntity $resource): bool {
        foreach ($this->getRequiredMetadataIds() as $metadataId) {
            if (!array_key_exists($metadataId, $resource->getContents())) {
                return false;
            }
        }
        return true;
    }

    public static function fromArray(array $data) {
        return new self($data['label'], $data['id'] ?? null, $data['requiredMetadataIds'] ?? []);
    }
}
