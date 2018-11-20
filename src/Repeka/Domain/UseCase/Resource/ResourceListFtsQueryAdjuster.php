<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandAdjuster;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Metadata\SearchValueAdjuster\SearchValueAdjusterComposite;
use Repeka\Domain\Repository\MetadataRepository;

class ResourceListFtsQueryAdjuster implements CommandAdjuster {
    /** @var MetadataRepository */
    private $metadataRepository;
    /** @var SearchValueAdjusterComposite */
    private $searchValueAdjusterComposite;

    public function __construct(MetadataRepository $metadataRepository, SearchValueAdjusterComposite $searchValueAdjusterComposite) {
        $this->metadataRepository = $metadataRepository;
        $this->searchValueAdjusterComposite = $searchValueAdjusterComposite;
    }

    /**
     * @param ResourceListFtsQuery $query
     * @return ResourceListFtsQuery
     */
    public function adjustCommand(Command $query): Command {
        return new ResourceListFtsQuery(
            $query->getPhrase(),
            $this->replaceMetadataNamesOrIdsWithMetadata($query->getSearchableMetadata()),
            $this->adjustMetadataFilters($query->getMetadataFilters()),
            $query->getResourceClasses(),
            $query->hasResourceKindFacet(),
            $this->replaceMetadataNamesOrIdsWithMetadata($query->getFacetedMetadata()),
            $this->adjustMetadataFilters($query->getFacetsFilters()),
            $query->isOnlyTopLevel(),
            $query->getPage(),
            $query->getResultsPerPage()
        );
    }

    private function replaceMetadataNamesOrIdsWithMetadata(array $searchableMetadata): array {
        $metadata = [];
        foreach ($searchableMetadata as $metadataNameOrId) {
            if (!$metadataNameOrId instanceof Metadata) {
                $metadata[] = $this->metadataRepository->findByNameOrId($metadataNameOrId);
            } else {
                $metadata[] = $metadataNameOrId;
            }
        }
        return $metadata;
    }

    private function adjustMetadataFilters(array $facetsFilters): array {
        $kindIdFilter = $facetsFilters['kindId'] ?? false;
        unset($facetsFilters['kindId']);
        $metadataNamesOrIds = array_keys($facetsFilters);
        $filters = array_values($facetsFilters);
        $metadataList = $this->replaceMetadataNamesOrIdsWithMetadata($metadataNamesOrIds);
        $filters = array_map(
            function (Metadata $metadata, $filter) {
                return [$metadata, $filter];
            },
            $metadataList,
            $filters
        );
        $filters = $this->adjustMetadataFiltersValues($filters);
        if ($kindIdFilter) {
            $filters[] = ['kindId', $kindIdFilter];
        }
        return $filters;
    }

    private function adjustMetadataFiltersValues(array $facetsFilters): array {
        $newFacetsFilters = [];
        /** @var Metadata $metadata */
        foreach ($facetsFilters as [$metadata, $value]) {
            $newValue = $this->searchValueAdjusterComposite->adjustSearchValue($value, $metadata->getControl());
            if ($newValue !== null) {
                $newFacetsFilters[] = [$metadata, $newValue];
            }
        }
        return $newFacetsFilters;
    }
}
