<?php
namespace Repeka\Application\Elasticsearch\Model;

use Repeka\Application\Elasticsearch\Mapping\ResourceConstants;

class FloatIndexedMetadata extends IndexedMetadata {
    public function __construct(string $typeName, $value = null) {
        parent::__construct($typeName, 'is_float', $value);
    }

    public static function getRequiredMapping(array $languages): array {
        return [ResourceConstants::FLOAT => ['type' => 'double']];
    }

    public function toArray(): array {
        $result = parent::toArray();
        $result[ResourceConstants::FLOAT] = $this->getValue();
        return $result;
    }
}
