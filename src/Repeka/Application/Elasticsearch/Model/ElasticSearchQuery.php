<?php
namespace Repeka\Application\Elasticsearch\Model;

use Elastica\Aggregation\Filters;
use Elastica\Aggregation\Terms;
use Elastica\Query;
use Repeka\Application\Elasticsearch\Mapping\FtsConstants;
use Repeka\Application\Elasticsearch\Model\ElasticSearchQueryCreator\ElasticSearchQueryCreatorComposite;
use Repeka\Application\Entity\UserEntity;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\UseCase\Resource\ResourceListFtsQuery;

/** @SuppressWarnings(PHPMD.CouplingBetweenObjects) it really has to use all of these Elastica helpers... */
class ElasticSearchQuery {

    /** @var ResourceListFtsQuery */
    private $query;

    /** @var ElasticSearchQueryCreatorComposite */
    private $elasticSearchQueryCreatorComposite;

    public function __construct(
        ResourceListFtsQuery $query,
        ElasticSearchQueryCreatorComposite $elasticSearchQueryCreatorComposite
    ) {
        $this->query = $query;
        $this->elasticSearchQueryCreatorComposite = $elasticSearchQueryCreatorComposite;
    }

    public function getQuery(): Query {
        $boolQuery = new Query\BoolQuery();
        $executor = $this->query->getExecutor();
        if ($executor) {
            $boolQuery->addFilter($this->buildVisibilityFilterQuery($executor));
        }
        if ($this->query->getPhrase()) {
            $boolQuery->addMust($this->atLeastOneMetadataShouldMatchThePhrase());
        }
        if ($this->query->getResourceClasses()) {
            $boolQuery->addFilter(new Query\Terms(FtsConstants::RESOURCE_CLASS, $this->query->getResourceClasses()));
        }
        if ($this->query->getMetadataFilters()) {
            $boolQuery->addFilter($this->buildMetadataFilters());
        }
        if ($this->query->isOnlyTopLevel()) {
            $boolQuery->addMustNot(new Query\Exists($this->getMetadataPath(SystemMetadata::PARENT()->toMetadata())));
        }
        $finalQuery = new Query($boolQuery);
        $finalQuery->setHighlight(['fields' => [FtsConstants::CONTENTS . '.*' => new \stdClass()]]);
        $facetFilters = $this->createFacetFilters();
        $facetDefinitions = $this->createFacetDefinitions($facetFilters);
        array_walk($facetDefinitions, [$finalQuery, 'addAggregation']);
        $postQueryFilter = new Query\BoolQuery();
        array_walk($facetFilters, [$postQueryFilter, 'addMust']);
        $finalQuery->setPostFilter($postQueryFilter);
        if ($this->query->paginate()) {
            $finalQuery->setSize($this->query->getResultsPerPage());
            $finalQuery->setFrom($this->query->getOffset());
        }
        return $finalQuery;
    }

    private function buildVisibilityFilterQuery(UserEntity $executor): Query\Terms {
        $visibilityMetadataPath = $this->getMetadataPath(SystemMetadata::VISIBILITY()->toMetadata());
        return new Query\Terms($visibilityMetadataPath, $executor->getGroupIdsWithUserId());
    }

    private function atLeastOneMetadataShouldMatchThePhrase(): Query\AbstractQuery {
        $metadataFilters = [];
        $metadataQuery = new Query\BoolQuery();
        foreach ($this->query->getSearchableMetadata() as $metadata) {
            $path = $this->getMetadataPath($metadata);
            if ($metadata->getControl() == MetadataControl::FILE) {
                $namePath = $path . '.' . FtsConstants::NAME;
                $contentPath = $path . '.' . FtsConstants::CONTENT;
                $metadataFilters[] = new Query\Match($namePath, $this->query->getPhrase());
                $metadataFilters[] = new Query\Match($contentPath, $this->query->getPhrase());
                $metadataFilters[] = new Query\Fuzzy($namePath, $this->query->getPhrase());
                $metadataFilters[] = new Query\Fuzzy($contentPath, $this->query->getPhrase());
            } else {
                $metadataFilters[] = new Query\Fuzzy($path, $this->query->getPhrase());
                $metadataFilters[] = new Query\Match($path, $this->query->getPhrase());
            }
        }
        $metadataQuery->addShould($metadataFilters);
        return $metadataQuery;
    }

    public static function getMetadataPath(Metadata $metadata): string {
        $metadataPath = $metadata->getId() . '.value_' . $metadata->getControl();
        while (!$metadata->isTopLevel()) {
            $parentMetadata = $metadata->getParent();
            $metadataPath = $parentMetadata->getId() . '.submetadata.' . $metadataPath;
            $metadata = $parentMetadata;
        }
        return FtsConstants::CONTENTS . '.' . $metadataPath;
    }

    /**
     * For every requested facet filter, create a Terms query that will be used to filter the search results according to the selection.
     * @return Query\Terms[]
     */
    private function createFacetFilters(): array {
        $facetFilters = [];
        foreach ($this->query->getFacetsFilters() as $facetFilter) {
            list($aggregationName, $aggregationFilters) = $facetFilter;
            if ($aggregationName == FtsConstants::KIND_ID) {
                $filter = new Query\Terms($aggregationName, $aggregationFilters);
            } else {
                $metadata = $aggregationName;
                $aggregationName = $metadata->getId();
                $filter = new Query\Terms($this->getMetadataPath($metadata), $aggregationFilters);
            }
            $facetFilters[$aggregationName] = $filter;
        }
        return $facetFilters;
    }

    /**
     * Creates a facets definitions with ES aggregations feature. The definitions are nested, so they can respond to the filters of other
     * facets.
     *
     * Consider an example:
     * Resource 1: {color: red, thing: flower}
     * Resource 2: {color: red, thing: shirt}
     * Resource 3: {color: blue: thing: flower}
     * Resource 4: {color: blue: thing:: shirt}
     *
     * When they all appear in the search results, there are two facets:
     * COLOR: red (2), blue (2)
     * THING: flower (2), shirt (2)
     *
     * When now user filters by red, color, the facets should be:
     * COLOR: red (2) (remove), blue (2)
     * THING: flower (1), shirt (1)
     *
     * So we do not want to limit current filter (the blue color is still there), but other filters should respond to changes in others
     * (there is only 1 red flower and 1 red shirt, we filtered out blues here)
     *
     * @see https://madewithlove.be/faceted-search-using-elasticsearch/
     * @param $facetFilters
     * @return Filters[]
     */
    private function createFacetDefinitions(array $facetFilters): array {
        $facetedFields = $this->query->getFacetedMetadata();
        if ($this->query->hasResourceKindFacet()) {
            $facetedFields[] = FtsConstants::KIND_ID;
        }
        $facetAggregations = [];
        foreach ($facetedFields as $facetedMetadata) {
            $aggregationName = $facetedMetadata == FtsConstants::KIND_ID ? FtsConstants::KIND_ID : $facetedMetadata->getId();
            $aggregationFieldPath = $facetedMetadata == FtsConstants::KIND_ID
                ? FtsConstants::KIND_ID
                : $this->getMetadataPath($facetedMetadata);
            $facetAggregation = new Filters($aggregationName);
            $filtersForThisFacet = array_diff_key($facetFilters, [$aggregationName => '']);
            foreach ($filtersForThisFacet as $filter) {
                $facetAggregation->addFilter($filter);
            }
            if (!$filtersForThisFacet) {
                $facetAggregation->addFilter(new Query\MatchAll());
            }
            $facetAggregation->addAggregation((new Terms($aggregationName))->setField($aggregationFieldPath));
            $facetAggregations[] = $facetAggregation;
        }
        return $facetAggregations;
    }

    private function buildMetadataFilters(): Query\AbstractQuery {
        $metadataFilters = new Query\BoolQuery();
        foreach ($this->query->getMetadataFilters() as $filterDef) {
            list($metadata, $filter) = $filterDef;
            if (!$filter) {
                continue;
            }
            if (!is_array($filter)) {
                $filter = [$filter];
            }
            $metadataFilter = $this->elasticSearchQueryCreatorComposite->createSearchQuery($filter, $metadata);
            $metadataFilters->addMust($metadataFilter);
        }
        return $metadataFilters;
    }
}
