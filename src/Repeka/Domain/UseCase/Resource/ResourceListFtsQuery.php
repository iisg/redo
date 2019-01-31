<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Constants\SystemRole;
use Repeka\Domain\Cqrs\AdjustableCommand;
use Repeka\Domain\Cqrs\RequireOperatorRole;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\UseCase\Audit\AbstractListQuery;

/** @SuppressWarnings(PHPMD.ExcessiveParameterList) */
class ResourceListFtsQuery extends AbstractListQuery implements AdjustableCommand {
    use RequireOperatorRole;

    /** @var string */
    private $phrase;
    /** @var array */
    private $searchableMetadata = [];
    /** @var array */
    private $metadataFilters;
    /** @var array */
    private $resourceClasses = [];
    /** @var array */
    private $facetedMetadata = [];
    /** @var array */
    private $facetsFilters = [];
    /** @var bool */
    private $resourceKindFacet;
    /** @var bool */
    private $onlyTopLevel;

    /**
     * @SuppressWarnings("PHPMD.BooleanArgumentFlag")
     */
    public function __construct(
        string $phrase,
        array $searchableMetadata,
        array $metadataFilters = [],
        array $resourceClasses = [],
        bool $resourceKindFacet = false,
        array $facetedMetadata = [],
        array $facetsFilters = [],
        bool $onlyTopLevel = false,
        int $page = 0,
        int $resultsPerPage = 10
    ) {
        parent::__construct($page, $resultsPerPage);
        $this->phrase = $phrase;
        $this->searchableMetadata = $searchableMetadata;
        $this->metadataFilters = $metadataFilters;
        $this->resourceClasses = $resourceClasses;
        $this->resourceKindFacet = $resourceKindFacet;
        $this->facetedMetadata = $facetedMetadata;
        $this->facetsFilters = $facetsFilters;
        $this->onlyTopLevel = $onlyTopLevel;
    }

    public static function builder(): ResourceListFtsQueryBuilder {
        return new ResourceListFtsQueryBuilder();
    }

    public function getPhrase(): string {
        return $this->phrase;
    }

    /** @return Metadata[] */
    public function getSearchableMetadata(): array {
        return $this->searchableMetadata;
    }

    public function getMetadataFilters(): array {
        return $this->metadataFilters;
    }

    public function getResourceClasses(): array {
        return $this->resourceClasses;
    }

    public function hasResourceKindFacet(): bool {
        return $this->resourceKindFacet;
    }

    /** @return Metadata[] */
    public function getFacetedMetadata(): array {
        return $this->facetedMetadata;
    }

    public function getFacetsFilters(): array {
        return $this->facetsFilters;
    }

    public function isOnlyTopLevel(): bool {
        return $this->onlyTopLevel;
    }

    public function getRequiredRole(): ?SystemRole {
        return null;
    }
}
