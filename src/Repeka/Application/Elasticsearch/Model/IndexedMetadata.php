<?php
namespace Repeka\Application\Elasticsearch\Model;

use Repeka\Application\Elasticsearch\Mapping\ResourceConstants;

abstract class IndexedMetadata implements IndexedMetadataAggregate {
    /** @var string */
    private $typeName;
    /** @var callable */
    private $typeValidatorFn;
    /** @var mixed */
    private $value;

    /** @var IndexedMetadata[] */
    private $children = [];

    public function __construct(string $typeName, $typeValidatorFn, $value = null) {
        $this->typeValidatorFn = $typeValidatorFn;
        $this->typeName = $typeName;
        if ($value !== null) {
            $this->setValue($value);
        }
    }

    public function getTypeName(): string {
        return $this->typeName;
    }

    public function getValue() {
        return $this->value;
    }

    public function setValue($value) {
        $validator = $this->typeValidatorFn;
        if (!($validator($value))) {
            throw new \InvalidArgumentException('Invalid value type');
        }
        $this->value = $value;
    }

    public function addMetadata(IndexedMetadata $metadata) {
        $this->children[] = $metadata;
    }

    /** @SuppressWarnings("PHPMD.UnusedFormalParameter") */
    public static function getRequiredMapping(array $languages): array {
        return [];
    }

    public function toArray(): array {
        $result = [ResourceConstants::VALUE_TYPE => $this->typeName];
        if (count($this->children) > 0) {
            $childrenArrays = [];
            foreach ($this->children as $child) {
                $childrenArrays[] = $child->toArray();
            }
            $result[ResourceConstants::CHILDREN] = $childrenArrays;
        }
        return $result;
    }
}
