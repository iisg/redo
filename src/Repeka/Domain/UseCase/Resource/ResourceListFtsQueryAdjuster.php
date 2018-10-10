<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandAdjuster;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Utils\EntityUtils;

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
            $query->getResourceClasses(),
            $query->hasResourceKindFacet(),
            $this->replaceMetadataNamesOrIdsWithMetadata($query->getFacetedMetadata()),
            $this->adjustFacetsFilters($query->getFacetsFilters()),
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

    private function adjustFacetsFilters(array $facetsFilters): array {
        $kindIdFilter = $facetsFilters['kindId'] ?? false;
        unset($facetsFilters['kindId']);
        $metadataNamesOrIds = array_keys($facetsFilters);
        $filters = array_values($facetsFilters);
        $metadataIds = EntityUtils::mapToIds($this->replaceMetadataNamesOrIdsWithMetadata($metadataNamesOrIds));
        if ($kindIdFilter) {
            $metadataIds[] = 'kindId';
            $filters[] = $kindIdFilter;
        }
        return array_combine($metadataIds, $filters);
    }
}
