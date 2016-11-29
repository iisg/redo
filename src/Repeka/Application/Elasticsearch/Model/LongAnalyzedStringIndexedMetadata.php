<?php
namespace Repeka\Application\Elasticsearch\Model;

use Repeka\Application\Elasticsearch\Mapping\ResourceConstants;

class LongAnalyzedStringIndexedMetadata extends AnalyzedStringIndexedMetadata {
    /** @var int */
    private $pageNumber;

    public function __construct(string $typeName, string $language, $value, $pageNumber) {
        parent::__construct($typeName, $language, $value);
        $this->setPageNumber($pageNumber);
    }

    public function getPageNumber(): int {
        return $this->pageNumber;
    }

    public function setPageNumber(int $pageNumber) {
        $this->pageNumber = $pageNumber;
    }

    public static function getRequiredMapping(array $languages): array {
        $fields = [ResourceConstants::INTEGER => ['type' => 'long']];
        foreach ($languages as $language => $analyzer) {
            $fieldName = ResourceConstants::longLanguageString($language);
            $fields[$fieldName] = ['type' => 'string'];
            if ($analyzer) {
                $fields[$fieldName]['analyzer'] = $analyzer;
            }
        }
        return $fields;
    }

    public function toArray(): array {
        $result = parent::toArray();
        $shortFieldName = ResourceConstants::languageString($this->getLanguage());
        $longFieldName = ResourceConstants::longLanguageString($this->getLanguage());
        $result[$longFieldName] = $result[$shortFieldName];
        unset($result[$shortFieldName]);
        $result[ResourceConstants::INTEGER] = $this->pageNumber;
        return $result;
    }
}
