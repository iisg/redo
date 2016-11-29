<?php
namespace Repeka\Application\Elasticsearch\Model;

use Repeka\Application\Elasticsearch\Mapping\ResourceConstants;

class TokenizedStringIndexedMetadata extends IndexedMetadata {
    public function __construct(string $typeName, $value = null) {
        parent::__construct($typeName, 'is_string', $value);
    }

    public static function getRequiredMapping(array $languages): array {
        return [ResourceConstants::TOKENIZED_STRING => ['type' => 'string', 'analyzer' => 'simple']];
    }

    public function toArray(): array {
        $result = parent::toArray();
        $result[ResourceConstants::TOKENIZED_STRING] = $this->getValue();
        return $result;
    }
}
