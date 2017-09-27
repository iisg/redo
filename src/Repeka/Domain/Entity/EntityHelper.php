<?php
namespace Repeka\Domain\Entity;

class EntityHelper {
    /**
     * Maps each ID to entity with such ID. All IDs must have matching entities.
     * Eg.  $ids = [2, 3];
     *      $entities = [ ['id' => 1], ['id' => 2], ['id' => 3] ];
     *      getByids($ids, $entities) == [ ['id' => 2], ['id' => 3] ];
     * @param (int|string)[] $ids
     * @param Identifiable[] $entities
     * @return Identifiable[]
     */
    public static function getByIds(array $ids, array $entities): array {
        $lookupMap = self::getLookupMap($entities);
        return array_map(function ($id) use ($lookupMap) {
            if (!array_key_exists($id, $lookupMap)) {
                throw new \OutOfBoundsException("No entity with key $id found");
            }
            return $lookupMap[$id];
        }, $ids);
    }

    /**
     * Returns an associative array ID => entity with that ID
     * Eg.  $entities = [ ['id' => 1], ['id' => 2], ['id' => 3] ];
     *      getLookupMap($entities) == [
     *          1 => ['id' => 1],
     *          2 => ['id' => 2],
     *          3 => ['id' => 3]
     *      ];
     *
     * @param Identifiable[] $entities
     * @return Identifiable[]
     */
    public static function getLookupMap(array $entities): array {
        $ids = self::mapToIds($entities);
        return array_combine($ids, $entities);
    }

    /**
     * Maps entities to their IDs, preserving order.
     * @param Identifiable[] $entities
     * @return (int|string)[]
     */
    public static function mapToIds(array $entities) {
        return array_map(function ($entity) {
            /** @var Identifiable $entity */
            return $entity->getId();
        }, $entities);
    }

    /** Just like getByIds(), but silently ignores missing items */
    public static function filterByIds(array $ids, array $entities): array {
        $commonIds = array_intersect($ids, self::mapToIds($entities));
        return self::getByIds($commonIds, $entities);
    }
}
