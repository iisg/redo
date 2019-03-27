<?php
namespace Repeka\Application\Elasticsearch\Model\ElasticSearchQueryCreator;

use Elastica\Query;
use Repeka\Application\Elasticsearch\Model\ElasticSearchContext;
use Repeka\Application\Elasticsearch\Model\ElasticSearchQuery;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;

class DefaultElasticSearchQueryCreator implements ElasticSearchQueryCreator {
    public function supports(MetadataControl $control): bool {
        return false;
    }

    public function createSearchQuery($filter, Metadata $metadata, ElasticSearchContext $elasticSearchContext) {
        $metadataFilter = new Query\BoolQuery();
        $metadataFilter->addShould(
            [
                new Query\Terms(ElasticSearchQuery::getMetadataPath($metadata), $filter),
            ]
        );
        return $metadataFilter;
    }
}
