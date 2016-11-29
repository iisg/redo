<?php
namespace Repeka\Application\Elasticsearch\Model;

use Repeka\Application\Elasticsearch\Mapping\ResourceConstants;

class DateTimeIndexedMetadata extends IndexedMetadata {
    public function __construct(string $typeName, $value = null) {
        $validator = function ($value) {
            return is_a($value, 'DateTime');
        };
        parent::__construct($typeName, $validator, $value);
    }

    public static function getRequiredMapping(array $languages): array {
        return [ResourceConstants::DATETIME => ['type' => 'date']];
    }

    public function toArray(): array {
        $result = parent::toArray();
        $result[ResourceConstants::DATETIME] = $this->getValue()->getTimestamp() * 1000;
        return $result;
    }
}
