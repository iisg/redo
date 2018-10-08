<?php
namespace Repeka\Application\Elasticsearch\Model;

use Elastica\Query;
use Repeka\Application\Elasticsearch\Mapping\ResourceConstants;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\UseCase\Resource\ResourceListFtsQuery;

class ElasticSearchQuery {
    /** @var ResourceListFtsQuery */
    private $query;

    public function __construct(ResourceListFtsQuery $query) {
        $this->query = $query;
    }

    public function getQuery(): Query {
        $boolQuery = new Query\BoolQuery();
        $boolQuery->addMust($this->atLeastOneMetadataShouldMatchThePhrase());
        if ($this->query->getResourceClasses()) {
            $boolQuery->addFilter(new Query\Terms(ResourceConstants::RESOURCE_CLASS, $this->query->getResourceClasses()));
        }
        $finalQuery = new Query($boolQuery);
        $finalQuery->setHighlight(['fields' => [ResourceConstants::CONTENTS . '.*' => new \stdClass()]]);
        if ($this->query->paginate()) {
            $finalQuery->setSize($this->query->getResultsPerPage());
            $finalQuery->setFrom($this->query->getOffset());
        }
        return $finalQuery;
    }

    private function atLeastOneMetadataShouldMatchThePhrase(): Query\AbstractQuery {
        $metadataFilters = [];
        foreach ($this->query->getSearchableMetadata() as $metadata) {
            $metadataFilters[] = new Query\Fuzzy($this->getMetadataPath($metadata), $this->query->getPhrase());
            $metadataFilters[] = new Query\Match($this->getMetadataPath($metadata), $this->query->getPhrase());
        }
        $metadataQuery = new Query\BoolQuery();
        $metadataQuery->addShould($metadataFilters);
        return $metadataQuery;
    }

    private function getMetadataPath(Metadata $metadata): string {
        $metadataPath = $metadata->getId() . '.value_' . $metadata->getControl();
        while (!$metadata->isTopLevel()) {
            $parentMetadata = $metadata->getParent();
            $metadataPath = $parentMetadata->getId() . '.submetadata.' . $metadataPath;
            $metadata = $parentMetadata;
        }
        return ResourceConstants::CONTENTS . '.' . $metadataPath;
    }
}
