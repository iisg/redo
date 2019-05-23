<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\RequireOperatorRole;
use Repeka\Domain\UseCase\Audit\AbstractListQueryBuilder;

/** @SuppressWarnings(PHPMD.ExcessiveParameterList) */
class ResourceListFtsQueryBuilder extends AbstractListQueryBuilder {
    use RequireOperatorRole;

    /** @var array */
    private $phrases = [];
    /** @var array */
    private $searchableMetadata = [];
    /** @var array */
    private $resourceClasses = [];
    /** @var array */
    private $metadataFacets = [];
    /** @var bool */
    private $resourceKindFacet = false;
    /** @var bool */
    private $onlyTopLevel = false;
    /** @var array */
    private $facetsFilters = [];
    /** @var */
    private $metadataFilters = [];

    public function build(): ResourceListFtsQuery {
        return new ResourceListFtsQuery(
            $this->phrases,
            $this->searchableMetadata,
            $this->metadataFilters,
            $this->resourceClasses,
            $this->resourceKindFacet,
            $this->metadataFacets,
            $this->facetsFilters,
            $this->onlyTopLevel,
            $this->page,
            $this->resultsPerPage
        );
    }

    public function addPhrase(string $phrase): self {
        $this->phrases[] = $phrase;
        return $this;
    }

    public function setPhrase(string $phrase): self {
        $this->phrases = [$phrase];
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
    public function setResourceKindFacet(bool $hasResourceKindFacet = true): self {
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

    /** @SuppressWarnings("PHPMD.BooleanArgumentFlag") */
    public function setOnlyTopLevel(bool $onlyTopLevel = true): self {
        $this->onlyTopLevel = $onlyTopLevel;
        return $this;
    }
}
