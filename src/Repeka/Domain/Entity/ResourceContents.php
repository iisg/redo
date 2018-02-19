<?php
namespace Repeka\Domain\Entity;

use Assert\Assertion;

class ResourceContents implements \IteratorAggregate, \ArrayAccess, \JsonSerializable {
    private $contents = [];

    public function __construct(array $normalizedContents) {
        $this->contents = $normalizedContents;
    }

    public function filterOutEmptyMetadata(): ResourceContents {
        return new self(array_filter($this->contents, function ($values) {
            return count($values) > 0;
        }));
    }

    public function mapAllValues(callable $mapper): ResourceContents {
        return new self($this->mapAllValuesRecursive($mapper, $this->contents));
    }

    private function mapAllValuesRecursive(callable $mapper, array $contents): array {
        foreach ($contents as $metadataId => &$values) {
            foreach ($values as &$value) {
                $value['value'] = $mapper($value['value'], $metadataId);
                if (isset($value['submetadata'])) {
                    $value['submetadata'] = $this->mapAllValuesRecursive($mapper, $value['submetadata']);
                }
            }
        }
        return $contents;
    }

    public function reduceAllValues(callable $function, $initial = null) {
        return $this->reduceAllValuesRecursive($this->contents, $function, $initial);
    }

    private function reduceAllValuesRecursive(array $contents, callable $function, $initial) {
        $result = $initial;
        foreach ($contents as $metadataId => $values) {
            foreach ($values as $value) {
                $result = $function($value['value'], $metadataId, $result);
                if (isset($value['submetadata'])) {
                    $result = $this->reduceAllValuesRecursive($value['submetadata'], $function, $result);
                }
            }
        }
        return $result;
    }

    public function forEachValue(callable $callback) {
        $this->reduceAllValuesRecursive($this->contents, $callback, null);
    }

    /**
     * @param Metadata|int $metadata
     * @return mixed[]
     */
    public function getValues($metadata): array {
        $desiredMetadataId = $metadata instanceof Metadata ? $metadata->getId() : $metadata;
        return $this->reduceAllValues(function ($value, int $metadataId, array $values) use ($desiredMetadataId) {
            if ($metadataId == $desiredMetadataId) {
                $values[] = $value;
            }
            return $values;
        }, []);
    }

    public function toArray(): array {
        return $this->contents;
    }

    /**
     * @param int|Metadata $metadata
     * @param mixed $values
     * @return ResourceContents
     */
    public function withNewValues($metadata, $values): ResourceContents {
        $metadataId = $metadata instanceof Metadata ? $metadata->getId() : $metadata;
        $newValues = self::fromArray([$metadataId => $values])->toArray();
        return new self(array_replace($this->contents, $newValues));
    }

    public static function fromArray(array $anyArray): ResourceContents {
        $normalized = array_map(function ($metadataEntry) {
            if (is_array($metadataEntry)) {
                return array_map(function ($metadataValue) {
                    if (is_array($metadataValue)) {
                        if (isset($metadataValue['submetadata'])) {
                            $metadataValue['submetadata'] = self::fromArray($metadataValue['submetadata'])->toArray();
                        }
                        if (!isset($metadataValue['value'])) {
                            $metadataValue['value'] = null;
                        }
                        return $metadataValue;
                    } else {
                        return ['value' => $metadataValue];
                    }
                }, $metadataEntry);
            } else {
                return [['value' => $metadataEntry]];
            }
        }, $anyArray);
        return new self($normalized);
    }

    public static function empty(): ResourceContents {
        return new self([]);
    }

    public function getIterator() {
        return new \ArrayIterator($this->contents);
    }

    public function offsetExists($offset) {
        return isset($this->contents[$offset]);
    }

    public function offsetGet($offset) {
        Assertion::keyIsset($this->contents, $offset);
        return $this->contents[$offset];
    }

    public function offsetSet($offset, $value) {
        throw new \LogicException('ResourceContents are immutable.');
    }

    public function offsetUnset($offset) {
        throw new \LogicException('ResourceContents are immutable.');
    }

    public function jsonSerialize() {
        return $this->contents;
    }
}
