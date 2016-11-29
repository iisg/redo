<?php
namespace Repeka\Application\Elasticsearch\Model;

use Repeka\Application\Elasticsearch\Mapping\ResourceConstants;

class IntIndexedMetadata extends IndexedMetadata {
    public function __construct(string $typeName, $value = null) {
        parent::__construct($typeName, 'is_int', $value);
    }

    public static function getRequiredMapping(array $languages): array {
        return [ResourceConstants::INTEGER => ['type' => 'long']];
    }

    public function toArray(): array {
        $result = parent::toArray();
        $result[ResourceConstants::INTEGER] = $this->getValue();
        return $result;
    }
}
