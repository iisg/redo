<?php
namespace Repeka\Application\Elasticsearch\Model;

use Assert\Assertion;
use Repeka\Application\Elasticsearch\Mapping\ResourceConstants;

class AnalyzedStringIndexedMetadata extends IndexedMetadata {
    /** @var string */
    private $language;

    public function __construct(string $typeName, string $language, $value) {
        Assertion::notBlank($language, 'Language must not be blank');
        parent::__construct($typeName, 'is_string');
        $this->language = $language;
        $this->setValue($value);
    }

    public function getLanguage(): string {
        return $this->language;
    }

    public static function getRequiredMapping(array $languages): array {
        $fields = [ResourceConstants::INTEGER => ['type' => 'long']];
        foreach ($languages as $language => $analyzer) {
            $fieldName = ResourceConstants::languageString($language);
            $fields[$fieldName] = ['type' => 'string'];
            if ($analyzer) {
                $fields[$fieldName]['analyzer'] = $analyzer;
            }
        }
        return $fields;
    }

    public function toArray(): array {
        $result = parent::toArray();
        $result[ResourceConstants::languageString($this->language)] = $this->getValue();
        return $result;
    }
}
