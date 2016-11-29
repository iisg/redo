<?php
namespace Repeka\Application\Elasticsearch\Model;

use Repeka\Application\Elasticsearch\Mapping\ResourceConstants;

class RawStringIndexedMetadata extends IndexedMetadata {
    public function __construct(string $typeName, $value = null) {
        parent::__construct($typeName, 'is_string', $value);
    }

    public static function getRequiredMapping(array $languages): array {
        return [ResourceConstants::RAW_STRING => ['type' => 'string', 'index' => 'not_analyzed']];
    }

    public function toArray(): array {
        $result = parent::toArray();
        $result[ResourceConstants::RAW_STRING] = $this->getValue();
        return $result;
    }
}
