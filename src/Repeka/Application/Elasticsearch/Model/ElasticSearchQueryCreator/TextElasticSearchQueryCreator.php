<?php
namespace Repeka\Application\Elasticsearch\Model\ElasticSearchQueryCreator;

use Elastica\Query;
use Repeka\Application\Elasticsearch\Model\ElasticSearchContext;
use Repeka\Application\Elasticsearch\Model\ElasticSearchQuery;
use Repeka\Application\Elasticsearch\Model\ElasticSearchTextQueryCreator;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;

class TextElasticSearchQueryCreator implements ElasticSearchQueryCreator {
    public function supports(MetadataControl $control): bool {
        return in_array($control->getValue(), [MetadataControl::TEXT, MetadataControl::TEXTAREA]);
    }

    public function createSearchQuery($filter, Metadata $metadata, ElasticSearchContext $elasticSearchContext) {
        $metadataFilter = new Query\BoolQuery();
        $path = ElasticSearchQuery::getMetadataPath($metadata);
        $elasticSearchTextQueryCreator = new ElasticSearchTextQueryCreator();
        $metadataFilter->addShould(
            $elasticSearchTextQueryCreator->createTextQuery([$path], $filter, $elasticSearchContext->getStopWords())
        );
        return $metadataFilter;
    }
}
