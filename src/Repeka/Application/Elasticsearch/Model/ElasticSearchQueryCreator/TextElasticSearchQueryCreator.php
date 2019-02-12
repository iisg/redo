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
        foreach ($filter as $phrase) {
            $metadataFilter->addShould(
                [
                    new Query\Fuzzy(ElasticSearchQuery::getMetadataPath($metadata), $phrase),
                    new Query\Match(ElasticSearchQuery::getMetadataPath($metadata), $phrase),
                ]
            );
        }
        return $metadataFilter;
    }
}
