<?php
namespace Repeka\Domain\Utils;

use Countable;

final class ArrayUtils {
    private function __construct() {
    }

    /**
     * Maps values to keys and groups matching items, preserving keys.
     * Eg. array_group_by([-1,2,1], Math.abs) === [ 1 => [0=>1, 2=>-1], 2 => [1=>2] ]
     * @param array $values
     * @param callable $mapper
     * @return array
     */
    public static function groupBy(array $values, callable $mapper) {
        $groups = [];
        foreach ($values as $key => $value) {
            $groupingKey = $mapper($value);
            if (!array_key_exists($groupingKey, $groups)) {
                $groups[$groupingKey] = [];
            }
            $groups[$groupingKey][$key] = $value;
        }
        return $groups;
    }

    /**
     * Filters an array with Countable values (eg. array) leaving only equal or longer than $minLength.
     * @param Countable[]|array[] $values
     * @param int $minLengthInclusive
     * @return array
     */
    public static function removeValuesShorterThan(array $values, int $minLengthInclusive) {
        return array_filter(
            $values,
            function ($valueArray) use ($minLengthInclusive) {
                return count($valueArray) >= $minLengthInclusive;
            }
        );
    }

    /**
     * Returns true if all values after applying function are equal.
     * @param array $values
     * @param callable $mapper
     * @return bool
     */
    public static function allEqual(array $values, callable $mapper): bool {
        $uniqueValues = array_unique(array_map($mapper, $values));
        return count($uniqueValues) <= 1;
    }

    /**
     * Flats any array to be one-dimensoinal.
     * @see https://stackoverflow.com/a/1320156/878514
     * @param array $multidimensionalArray
     * @return array flat array
     */
    public static function flatten(array $multidimensionalArray): array {
        $result = [];
        array_walk_recursive(
            $multidimensionalArray,
            function ($a) use (&$result) {
                $result[] = $a;
            }
        );
        return $result;
    }

    /**
     * Creates array with given keys each having same value.
     *
     * Example: makeArray(['a', 'b', 'c'], 'X') --> ['a' => 'X', 'b' => 'X', 'c' => 'X']
     */
    public static function combineArrayWithSingleValue(array $keys, $value): array {
        $values = array_fill(0, count($keys), $value);
        return array_combine($keys, $values);
    }

    /**
     * Behaves like range function, but result is always ascending.
     * PHP function has arguably incorrect behavior:
     * range(4, 2), or even range(4, 2, 1) with explicit positive step, returns [4,3,2] instead of [].
     */
    public static function rangeAscending($start, $endInclusive): array {
        return $start <= $endInclusive ? range($start, $endInclusive) : [];
    }

    public static function filterOutValue(array $array, $valueToFilterOut): array {
        return array_filter(
            $array,
            function ($value) use ($valueToFilterOut) {
                return $value != $valueToFilterOut;
            }
        );
    }

    /**
     * Returns true if $search is nested array that contains corresponding key on each level,
     * eg. keyPathExists(['a', 'b', 'c'], ['a' => ['b' => ['c' => null]]] returns true
     */
    public static function keyPathExists(array $keys, array $search): bool {
        foreach ($keys as $key) {
            if (!is_array($search) || !array_key_exists($key, $search)) {
                return false;
            }
            $search = $search[$key];
        }
        return true;
    }

    /**
     * @param array|iterable $array
     * @param string|callable $keySpec
     * @return array
     */
    public static function keyBy($array, $keySpec): array {
        if (is_string($keySpec)) {
            $key = $keySpec;
            $keySpec = function ($elem) use ($key) {
                return $elem[$key];
            };
        }
        $result = [];
        foreach ($array as $item) {
            $newKey = $keySpec($item);
            $result[$newKey] = $item;
        }
        return $result;
    }
}
