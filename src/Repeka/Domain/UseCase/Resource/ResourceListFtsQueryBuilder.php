<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\RequireOperatorRole;
use Repeka\Domain\UseCase\Audit\AbstractListQueryBuilder;

/** @SuppressWarnings(PHPMD.ExcessiveParameterList) */
class ResourceListFtsQueryBuilder extends AbstractListQueryBuilder {
    use RequireOperatorRole;

    /** @var string */
    private $phrase;
    /** @var array */
    private $searchableMetadata = [];
    /** @var array */
    private $resourceClasses = [];
    /** @var array */
    private $metadataFacets = [];
    /** @var bool */
    private $resourceKindFacet = false;
    /** @var array */
    private $facetsFilters = [];
    /** @var */
    private $metadataFilters = [];

    public function build(): ResourceListFtsQuery {
        return new ResourceListFtsQuery(
            $this->phrase,
            $this->searchableMetadata,
            $this->metadataFilters,
            $this->resourceClasses,
            $this->resourceKindFacet,
            $this->metadataFacets,
            $this->facetsFilters,
            $this->page,
            $this->resultsPerPage
        );
    }

    public function setPhrase(string $phrase): self {
        $this->phrase = $phrase;
        return $this;
    }

    public function setSearchableMetadata(array $searchableMetadata): self {
        $this->searchableMetadata = $searchableMetadata;
        return $this;
    }

    public function setResourceClasses(array $resourceClasses): self {
        $this->resourceClasses = $resourceClasses;
        return $this;
    }

    /** @SuppressWarnings("PHPMD.BooleanArgumentFlag") */
    public function setResourceKindFacet($hasResourceKindFacet = true): self {
        $this->resourceKindFacet = $hasResourceKindFacet;
        return $this;
    }

    public function setMetadataFacets(array $facets): self {
        $this->metadataFacets = $facets;
        return $this;
    }

    public function setFacetsFilters(array $facetsFilters): self {
        $this->facetsFilters = $facetsFilters;
        return $this;
    }

    public function setMetadataFilters(array $metadataFilters): self {
        $this->metadataFilters = $metadataFilters;
        return $this;
    }
}
