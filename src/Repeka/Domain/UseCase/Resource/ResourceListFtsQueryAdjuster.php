<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandAdjuster;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Repository\MetadataRepository;

class ResourceListFtsQueryAdjuster implements CommandAdjuster {
    /** @var MetadataRepository */
    private $metadataRepository;

    public function __construct(MetadataRepository $metadataRepository) {
        $this->metadataRepository = $metadataRepository;
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
        if ($kindIdFilter) {
            $filters[] = ['kindId', $kindIdFilter];
        }
        return $filters;
    }
}
