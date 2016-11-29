<?php
namespace Repeka\Application\Elasticsearch\Mapping;

final class ResourceConstants {
    const ES_DOCUMENT_TYPE = 'resource';

    const VALUE_TYPE = 'type';
    const CHILDREN = 'metadata';

    const INTEGER = 'value_integer';
    const FLOAT = 'value_float';
    const DATETIME = 'value_datetime';
    const RAW_STRING = 'value_raw_string';
    const TOKENIZED_STRING = 'value_tokenized_string';

    const STRING_PATTERN = 'value_string_%s';
    const LONG_STRING_PATTERN = 'value_long_string_%s';

    public static function languageString(string $language) {
        return sprintf(self::STRING_PATTERN, $language);
    }

    public static function longLanguageString(string $language) {
        return sprintf(self::LONG_STRING_PATTERN, $language);
    }
}
