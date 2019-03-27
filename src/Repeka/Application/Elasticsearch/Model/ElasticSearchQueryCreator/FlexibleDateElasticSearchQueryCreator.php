<?php
namespace Repeka\Application\Elasticsearch\Model\ElasticSearchQueryCreator;

use Elastica\Query;
use Repeka\Application\Elasticsearch\Mapping\FtsConstants;
use Repeka\Application\Elasticsearch\Model\ElasticSearchContext;
use Repeka\Application\Elasticsearch\Model\ElasticSearchQuery;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;

class FlexibleDateElasticSearchQueryCreator implements ElasticSearchQueryCreator {
    public function supports(MetadataControl $control): bool {
        return in_array($control->getValue(), [MetadataControl::FLEXIBLE_DATE, MetadataControl::DATE_RANGE]);
    }

    public function createSearchQuery($filter, Metadata $metadata, ElasticSearchContext $elasticSearchContext) {
        $metadataFilter = new Query\BoolQuery();
        $musts = [];
        if (isset($filter['gte'])) {
            $musts[] = $this->dateRangePartFilter($metadata, ['gte' => $filter['gte']], '.to');
        }
        if (isset($filter['lte'])) {
            $musts[] = $this->dateRangePartFilter($metadata, ['lte' => $filter['lte']], '.from');
        }
        $musts[] = new Query\Exists(ElasticSearchQuery::getMetadataPath($metadata));
        $metadataFilter->addMust($musts);
        return $metadataFilter;
    }

    private function dateRangePartFilter($metadata, $dateFilterArray, $rangePart): Query\BoolQuery {
        $dateFilterArray['format'] = FtsConstants::FLEXIBLE_DATE_FORMAT;
        $datePartFilter = new Query\BoolQuery();
        $shoulds = [];
        $notExistsBool = new Query\BoolQuery();
        $shoulds[] = $notExistsBool->addMustNot(new Query\Exists(ElasticSearchQuery::getMetadataPath($metadata) . $rangePart));
        $shoulds[] = new Query\Range(ElasticSearchQuery::getMetadataPath($metadata) . $rangePart, $dateFilterArray);
        $datePartFilter->addShould($shoulds);
        return $datePartFilter;
    }
}
