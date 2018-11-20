<?php
namespace Repeka\Application\Elasticsearch\Mapping;

use Elastica\Type\Mapping;

class ESMapping {

    /** @SuppressWarnings("PHPMD.ExcessiveMethodLength") */
    public static function getMapping($elasticaType): Mapping {
        $mapping = new Mapping();
        $mapping->setType($elasticaType);
        $mapping->setParam(FtsConstants::NUMERIC_DETECTION_PARAM, true);
        $mapping->setProperties(
            [
                FtsConstants::ID => [FtsConstants::VALUE_TYPE => 'long'],
                FtsConstants::RESOURCE_CLASS => [FtsConstants::VALUE_TYPE => 'keyword'],
                FtsConstants::CONTENTS => [FtsConstants::VALUE_TYPE => 'object', 'dynamic' => true],
                FtsConstants::KIND_ID => [FtsConstants::VALUE_TYPE => 'long'],
            ]
        );
        $mapping->setParam(
            'dynamic_templates',
            [
                [
                    'as_text' => [
                        'path_match' => '*.value_text',
                        'mapping' => [
                            'type' => 'text',
                            'fields' => [
                                'raw' => [
                                    'type' => 'keyword',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'as_textarea' => [
                        'path_match' => '*.value_textarea',
                        'mapping' => [
                            'type' => 'text',
                        ],
                    ],
                ],
                [
                    'as_integer' => [
                        'path_match' => '*.value_integer',
                        'mapping' => [
                            'type' => 'long',
                        ],
                    ],
                ],
                [
                    'as_timestamp' => [
                        'path_match' => '*.value_timestamp',
                        'mapping' => [
                            'type' => 'date',
                            'format' => FtsConstants::TIMESTAMP_FORMAT,
                        ],
                    ],
                ],
                [
                    'as_flexible-date' => [
                        'path_match' => '*.value_(flexible|range)-date',
                        'mapping' => [
                            'type' => 'object',
                            'properties' => [
                                'to' => [
                                    'type' => 'date',
                                    'format' => FtsConstants::FLEXIBLE_DATE_FORMAT,
                                ],
                                'from' => [
                                    'type' => 'date',
                                    'format' => FtsConstants::FLEXIBLE_DATE_FORMAT,
                                ],
                                'mode' => [
                                    'type' => 'text',
                                ],
                                'rangeMode' => [
                                    'type' => 'text',
                                ],
                                'displayValue' => [
                                    'type' => 'text',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'as_boolean' => [
                        'path_match' => '*.value_boolean',
                        'mapping' => [
                            'type' => 'boolean',
                        ],
                    ],
                ],
                [
                    'as_relationship' => [
                        'path_match' => '*.value_relationship',
                        'mapping' => [
                            'type' => 'long',
                        ],
                    ],
                ],
                [
                    'as_file' => [
                        'path_match' => '*.value_file',
                        'mapping' => [
                            'type' => 'object',
                            'properties' => [
                                'name' => [
                                    'analyzer' => 'underscore_analyzer',
                                    'type' => 'text',
                                ],
                                'content' => [
                                    'type' => 'text',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'as_wyswig-editor' => [
                        'path_match' => '*.value_wyswig-editor',
                        'mapping' => [
                            'type' => 'text',
                        ],
                    ],
                ],
                [
                    'as_display-strategy' => [
                        'path_match' => '*.value_boolean',
                        'mapping' => [
                            'type' => 'text',
                            'fields' => [
                                'raw' => [
                                    'type' => 'keyword',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
        return $mapping;
    }
}
