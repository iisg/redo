<?php
namespace Repeka\Domain\Entity;

use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Utils\ImmutableIteratorAggregate;

class ResourceContents extends ImmutableIteratorAggregate implements \JsonSerializable {
    public function filterOutEmptyMetadata(): ResourceContents {
        return self::fromArray($this->filterOutEmptyMetadataInContents($this->contents));
    }

    private function filterOutEmptyMetadataInContents(array $contents): array {
        foreach ($contents as &$values) {
            if (is_array($values)) {
                $firstKey = key($values);
                if (is_numeric($firstKey) || in_array($firstKey, ['value', 'submetadata'], true)) {
                    $values = $this->filterOutEmptyMetadataInContents($values);
                }
            }
        }
        return array_filter(
            $contents,
            function ($values) {
                if (is_array($values)) {
                    return count($values) > 0;
                } elseif (is_string($values)) {
                    return trim($values) !== '';
                } else {
                    return $values !== null;
                }
            }
        );
    }

    /**
     * @param callable $mapper function(MetadataValue $value, int $metadataId): MetadataValue
     *                         that receives every single value of all metadata from the contents and returns the mapped value
     * @return ResourceContents new instance of the contents with all values mapped by $mapper
     */
    public function mapAllValues(callable $mapper): ResourceContents {
        return new self($this->mapAllValuesRecursive($mapper, $this->contents));
    }

    private function mapAllValuesRecursive(callable $mapper, array $contents): array {
        foreach ($contents as $metadataId => &$values) {
            foreach ($values as &$value) {
                $value = $mapper(new MetadataValue($value), $metadataId);
                if ($value) {
                    $value = $value->toArray();
                    if (isset($value['submetadata'])) {
                        $value['submetadata'] = $this->mapAllValuesRecursive($mapper, $value['submetadata']);
                    }
                }
            }
            $values = array_values(
                array_filter(
                    $values,
                    function ($value) {
                        return $value !== null;
                    }
                )
            );
        }
        return $contents;
    }

    /**
     * @param callable $function function(MetadataValue $value, int $metadataId, mixed $accumulator)
     *                           that receives every value of all metadata from the contents and returns the new accumulator value
     * @param mixed $initial initial value for the accumulator
     * @return mixed the final accumulator value
     */
    public function reduceAllValues(callable $function, $initial = null) {
        return $this->reduceAllValuesRecursive($this->contents, $function, $initial);
    }

    private function reduceAllValuesRecursive(array $contents, callable $function, $initial) {
        $result = $initial;
        foreach ($contents as $metadataId => $values) {
            foreach ($values as $value) {
                $result = $function(new MetadataValue($value), $metadataId, $result);
                if (isset($value['submetadata'])) {
                    $result = $this->reduceAllValuesRecursive($value['submetadata'], $function, $result);
                }
            }
        }
        return $result;
    }

    /**
     * @param callable $callback function(MetadataValue $value, int $metadataId) that receives every value of all metadata from the contents
     */
    public function forEachValue(callable $callback) {
        $this->reduceAllValuesRecursive($this->contents, $callback, null);
    }

    /**
     * @param callable $callback function(array $values, int $metadataId) that receives all values of all metadata from the contents
     */
    public function forEachMetadata(callable $callback) {
        $this->forEachMetadataRecursive($this->contents, $callback);
    }

    private function forEachMetadataRecursive(array $contents, callable $function) {
        foreach ($contents as $metadataId => $values) {
            $function(array_column($values, 'value'), $metadataId);
            foreach ($values as $value) {
                if (isset($value['submetadata'])) {
                    $this->forEachMetadataRecursive($value['submetadata'], $function);
                }
            }
        }
    }

    /**
     * @param Metadata|int $metadata
     * @return MetadataValue[]
     */
    public function getValues($metadata): array {
        $desiredMetadataId = $metadata instanceof Metadata ? $metadata->getId() : $metadata;
        return $this->reduceAllValues(
            function ($value, int $metadataId, array $values) use ($desiredMetadataId) {
                if ($metadataId == $desiredMetadataId) {
                    $values[] = $value;
                }
                return $values;
            },
            []
        );
    }

    /**
     * @param Metadata|int $metadata
     * @return mixed[]
     */
    public function getValuesWithoutSubmetadata($metadata): array {
        return array_map(
            function (MetadataValue $value) {
                return $value->getValue();
            },
            $this->getValues($metadata)
        );
    }

    public function toArray(): array {
        return $this->contents;
    }

    /**
     * @param int|Metadata $metadata
     * @param mixed $values
     * @return ResourceContents
     */
    public function withReplacedValues($metadata, $values): ResourceContents {
        $metadataId = $metadata instanceof Metadata ? $metadata->getId() : $metadata;
        $newValues = self::fromArray([$metadataId => $values])->toArray();
        return new self(array_replace($this->contents, $newValues));
    }

    /**
     * @param int|Metadata $metadata
     * @param mixed $values
     * @return ResourceContents
     */
    public function withMergedValues($metadata, $values): ResourceContents {
        $metadataId = $metadata instanceof Metadata ? $metadata->getId() : $metadata;
        $newValues = self::fromArray([$metadataId => $values])->toArray();
        $contents = $this->contents;
        $contents[$metadataId] = array_merge($contents[$metadataId] ?? [], $newValues[$metadataId]);
        return new self($contents);
    }

    /**
     * Remove duplicated metadata values, merging their submetadata if any.
     *
     * @param int|Metadata $metadata
     * @return ResourceContents
     *
     * Before:
     *  1 => [['value' => 'aaa'], ['value' => 'aaa']]
     *  2 => [
     *      ['value' => 'aaa', 'submetadata' => [4 => [['value' => 'ccc']]]],
     *      ['value' => 'aaa', 'submetadata' => [4 => [['value' => 'bbb']]]],
     *  ],
     *  3 => [
     *      ['value' => 'aaa', 'submetadata' => [5 => [['value' => 'ccc']]]],
     *      ['value' => 'aaa', 'submetadata' => [6 => [['value' => 'bbb']]]],
     *  ]
     * After:
     *  1 => [['value' => 'aaa']]
     *  2 => [['value' => 'aaa', 'submetadata' => [4 => [['value' => 'ccc'], ['value' => 'bbb']]]]],
     *  3 => [['value' => 'aaa', 'submetadata' => [5 => [['value' => 'ccc']], 6 => [['value' => 'bbb']]]]]
     */
    public function clearDuplicates($metadata): ResourceContents {
        $metadataId = $metadata instanceof Metadata ? $metadata->getId() : $metadata;
        $currentValues = $this->contents[$metadataId];
        $clearedValues = $this->mergeSubmetadataOfDuplicatedValues($currentValues);
        $newValues = self::fromArray([$metadataId => $clearedValues])->toArray();
        return new self(array_replace($this->contents, $newValues));
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @param array $values
     * @return array
     */
    private function mergeSubmetadataOfDuplicatedValues(array $values): array {
        $clearedValues = [];
        foreach ($values as $value) {
            $exists = false;
            for ($i = 0; $i < count($clearedValues); $i++) {
                $adjustedValue = $clearedValues[$i];
                if ($value['value'] == $adjustedValue['value']) {
                    $exists = true;
                    if (isset($value['submetadata']) && !isset($adjustedValue['submetadata'])) {
                        $clearedValues[$i]['submetadata'] = $value['submetadata'];
                    } elseif (isset($value['submetadata']) && isset($adjustedValue['submetadata'])) {
                        foreach ($value['submetadata'] as $metadataId => $submetadataValues) {
                            $clearedValues[$i]['submetadata'][$metadataId] = $this->mergeSubmetadataOfDuplicatedValues(
                                array_merge($adjustedValue['submetadata'][$metadataId] ?? [], $submetadataValues)
                            );
                        }
                    }
                    break;
                }
            }
            if (!$exists) {
                $clearedValues[] = $value;
            }
        }
        return $clearedValues;
    }

    public function isEmpty() {
        return empty($this->contents);
    }

    public function withMetadataNamesMappedToIds(MetadataRepository $metadataRepository): ResourceContents {
        $newContents = $this->withMetadataNamesMappedToIdsRecursive($metadataRepository, $this->contents);
        return new self($newContents);
    }

    private function withMetadataNamesMappedToIdsRecursive(MetadataRepository $metadataRepository, array $contents): array {
        $mappedContents = [];
        foreach ($contents as $metadataIdOrName => &$values) {
            foreach ($values as &$metadataValue) {
                if (isset($metadataValue['submetadata'])) {
                    $submetadataValue = $this->withMetadataNamesMappedToIdsRecursive(
                        $metadataRepository,
                        $metadataValue['submetadata']
                    );
                    if (empty($submetadataValue)) {
                        unset($metadataValue['submetadata']);
                    } else {
                        $metadataValue['submetadata'] = $submetadataValue;
                    }
                }
            }
            try {
                $metadata = $metadataRepository->findByNameOrId($metadataIdOrName);
                $mappedContents[$metadata->getId()] = $values;
                unset($contents[$metadataIdOrName]);
            } catch (EntityNotFoundException $e) {
            }
        }
        return $mappedContents;
    }

    public static function fromArray(array $anyArray, callable $valueProducer = null): ResourceContents {
        if (!$valueProducer) {
            $valueProducer = function ($metadataValue) {
                return is_array($metadataValue) ? ($metadataValue['value'] ?? null) : $metadataValue;
            };
        }
        $normalized = array_map(
            function ($metadataEntry) use ($valueProducer) {
                if (is_array($metadataEntry)) {
                    if (isset($metadataEntry['value']) || isset($metadataEntry['submetadata'])) {
                        $metadataEntry = [$metadataEntry];
                    }
                    return array_map(
                        function ($metadataValue) use ($valueProducer) {
                            if (is_array($metadataValue)) {
                                if (isset($metadataValue['submetadata'])) {
                                    $metadataValue['submetadata'] = self::fromArray($metadataValue['submetadata'], $valueProducer)
                                        ->toArray();
                                }
                                $metadataValue['value'] = $valueProducer($metadataValue);
                                return array_intersect_key($metadataValue, ['value' => '', 'submetadata' => '']);
                            } elseif ($metadataValue instanceof MetadataValue) {
                                return $metadataValue->toArray();
                            } else {
                                return ['value' => $valueProducer($metadataValue)];
                            }
                        },
                        $metadataEntry
                    );
                } else {
                    return [['value' => $valueProducer($metadataEntry)]];
                }
            },
            $anyArray
        );
        return new self($normalized);
    }

    public static function empty(): ResourceContents {
        return new self([]);
    }

    public function jsonSerialize() {
        return $this->contents;
    }
}
