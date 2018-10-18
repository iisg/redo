<?php
namespace Repeka\Domain\Service;

use Repeka\Domain\Entity\ResourceEntity;

class ResourceDisplayStrategyUsedMetadataCollector {
    private $usedMetadata = [];

    /**
     * @param ResourceEntity $resource
     */
    public function addUsedMetadata(int $metadataId, $resource): self {
        if ($metadataId && $resource instanceof ResourceEntity) {
            $this->usedMetadata[$metadataId][] = $resource->getId();
            $this->usedMetadata[$metadataId] = array_unique($this->usedMetadata[$metadataId]);
        }
        return $this;
    }

    /**
     * Returns metadata ids collected during the display strategy evaluation. The array contains all metadata ids and resource ids that
     * were used to produce the output value.
     *
     * The format is as follows:
     *
     * metadataId => [resourceIds]
     *
     * For example:
     * [
     *   12 => [1],
     *   13 => [1, 2],
     * ]
     * means that during the display strategy evaluation metadata 12 was used from resource 1 and metadata 13 was used from resource 1 and
     * 2.
     *
     * This information is then used to build the ResourceDisplayStrategyDependencyMap.
     *
     * @return array
     */
    public function getUsedMetadata(): array {
        return $this->usedMetadata;
    }
}
