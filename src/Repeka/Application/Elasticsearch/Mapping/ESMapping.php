<?php
namespace Repeka\Application\Elasticsearch\Mapping;

use Elastica\Type\Mapping;

class ESMapping {

    public static function getMapping($elasticaType): Mapping {
        $mapping = new Mapping();
        $mapping->setType($elasticaType);
        $mapping->setParam(ResourceConstants::NUMERIC_DETECTION_PARAM, true);
        $mapping->setProperties(
            [
                ResourceConstants::ID => [ResourceConstants::VALUE_TYPE => 'long'],
                ResourceConstants::RESOURCE_CLASS => [ResourceConstants::VALUE_TYPE => 'keyword'],
                ResourceConstants::CONTENTS => [ResourceConstants::VALUE_TYPE => 'object', 'dynamic' => true],
                ResourceConstants::KIND_ID => [ResourceConstants::VALUE_TYPE => 'long'],
            ]
        );
        return $mapping;
    }
}
