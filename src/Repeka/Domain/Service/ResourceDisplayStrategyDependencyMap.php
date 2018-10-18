<?php
namespace Repeka\Domain\Service;

use Repeka\Domain\Entity\ResourceEntity;

/**
 * This class is responsible for building and maintaining map of metadata values that are used to generate display strategies of a resource.
 * Such information enables us to recalculate dependent display strategies when we precisely know what has been changed and what needs
 * to be updated.
 *
 * The format of the array is as follows:
 * RESOURCE_ID/METADATA_ID => [IDS OF DEPENDENT METADATA WITH DISPLAY STRATEGY CONTROL]
 *
 * For example:
 * [
 *   '12/3' => [20, 21],
 *   '14/11' => [20],
 * ]
 * Such array means, that display strategies metadata of a resource with id 20 and 21 rely on the value of metadata 3 from resource 12.
 * Moreover, display strategy metadata with id 20 also depends on metadata 11 from resource 14.
 *
 * Such array is stored along with every resource and allows to quickly find metadata ids that needs recalculation. For example, if
 * the metadata 11 in resource 14 is changed during resource edit, we immediately know that the metadata 20 of the resource that owns this
 * map should be recalculated, too.
 */
class ResourceDisplayStrategyDependencyMap {
    private const DEPENDENCY_KEY_SEPARATOR = '/';

    /** @var int[][] */
    private $map = [];

    public function __construct($metadataIdOrMap, ResourceDisplayStrategyUsedMetadataCollector $collector = null) {
        if (is_array($metadataIdOrMap)) {
            $this->map = $metadataIdOrMap;
        } else {
            $this->build($metadataIdOrMap, $collector);
        }
    }

    private function build(int $metadataId, ResourceDisplayStrategyUsedMetadataCollector $collector): void {
        foreach ($collector->getUsedMetadata() as $usedMetadataId => $usedResourceIds) {
            foreach ($usedResourceIds as $usedResourceId) {
                $this->map[self::createDependencyKey($usedResourceId, $usedMetadataId)][] = $metadataId;
            }
        }
    }

    public static function createDependencyKey($resourceId, $metadataId): string {
        return $resourceId . self::DEPENDENCY_KEY_SEPARATOR . $metadataId;
    }

    public function merge(ResourceDisplayStrategyDependencyMap $map): self {
        return new self(array_merge_recursive($this->map, $map->toArray()));
    }

    public function clear(int $metadataId): self {
        $map = $this->map;
        foreach ($map as &$metadataIds) {
            $metadataIds = array_values(array_diff($metadataIds, [$metadataId]));
        }
        return new self(array_filter($map));
    }

    public function toArray(): array {
        return $this->map;
    }

    public function getDependentMetadataIds(ResourceEntity $resource, array $changedMetadataIds): array {
        $dependentKeys = array_map(
            function ($metadataId) use ($resource) {
                return self::createDependencyKey($resource->getId(), $metadataId);
            },
            $changedMetadataIds
        );
        $dependentMetadataIds = array_reduce(
            $dependentKeys,
            function ($acc, $dependentKey) {
                return array_merge($acc, $this->map[$dependentKey] ?? []);
            },
            []
        );
        return array_unique($dependentMetadataIds);
    }
}
