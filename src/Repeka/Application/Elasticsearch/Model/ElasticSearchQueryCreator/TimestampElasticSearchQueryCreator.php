<?php
namespace Repeka\Application\Elasticsearch\Model\ElasticSearchQueryCreator;

use Elastica\Query;
use Repeka\Application\Elasticsearch\Mapping\FtsConstants;
use Repeka\Application\Elasticsearch\Model\ElasticSearchContext;
use Repeka\Application\Elasticsearch\Model\ElasticSearchQuery;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;

class TimestampElasticSearchQueryCreator implements ElasticSearchQueryCreator {
    public function supports(MetadataControl $control): bool {
        return in_array($control->getValue(), [MetadataControl::TIMESTAMP]);
    }

    public function createSearchQuery($filter, Metadata $metadata, ElasticSearchContext $elasticSearchContext) {
        $metadataFilter = new Query\BoolQuery();
        $filter["format"] = FtsConstants::TIMESTAMP_FORMAT;
        $metadataFilter->addShould(
            [
                new Query\Range(ElasticSearchQuery::getMetadataPath($metadata), $filter),
            ]
        );
        return $metadataFilter;
    }
}
