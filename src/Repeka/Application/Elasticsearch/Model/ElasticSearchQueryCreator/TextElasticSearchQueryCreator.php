<?php
namespace Repeka\Application\Elasticsearch\Model\ElasticSearchQueryCreator;

use Elastica\Query;
use Repeka\Application\Elasticsearch\Model\ElasticSearchQuery;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;

class TextElasticSearchQueryCreator implements ElasticSearchQueryCreator {
    public function supports(MetadataControl $control): bool {
        return in_array($control->getValue(), [MetadataControl::TEXT, MetadataControl::TEXTAREA]);
    }

    public function createSearchQuery($filter, Metadata $metadata) {
        $metadataFilter = new Query\BoolQuery();
        $path = ElasticSearchQuery::getMetadataPath($metadata);
        foreach ($filter as $phrase) {
            $metadataFilter->addShould(
                [
                    ElasticSearchQuery::createSimpleQueryString([$path], $phrase),
                    ElasticSearchQuery::createMultiMatchQuery([$path], $phrase),
                ]
            );
        }
        return $metadataFilter;
    }
}
