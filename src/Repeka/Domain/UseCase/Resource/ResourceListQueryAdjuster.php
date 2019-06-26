<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandAdjuster;
use Repeka\Domain\Entity\MetadataValue;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Metadata\MetadataValueAdjuster\MetadataValueAdjusterComposite;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceKindRepository;

class ResourceListQueryAdjuster implements CommandAdjuster {

    /** @var MetadataRepository */
    private $metadataRepository;
    /** @var ResourceKindRepository */
    private $resourceKindRepository;
    /** @var MetadataValueAdjusterComposite */
    private $metadataValueAdjuster;

    public function __construct(
        MetadataRepository $metadataRepository,
        ResourceKindRepository $resourceKindRepository,
        MetadataValueAdjusterComposite $metadataValueAdjuster
    ) {
        $this->metadataRepository = $metadataRepository;
        $this->resourceKindRepository = $resourceKindRepository;
        $this->metadataValueAdjuster = $metadataValueAdjuster;
    }

    /**
     * @param ResourceListQuery $query
     * @return ResourceListQuery
     */
    public function adjustCommand(Command $query): Command {
        return ResourceListQuery::withParams(
            $query->getIds(),
            $query->getResourceClasses(),
            $this->convertResourceKindIdsToResourceKinds($query->getResourceKinds()),
            $this->convertSortByMetadataColumnsToIntegers($query->getSortBy()),
            $query->getParentId(),
            $this->adjustContents($query->getContentsFilters()),
            $query->onlyTopLevel(),
            $query->getPage(),
            $query->getResultsPerPage(),
            $query->getWorkflowPlacesIds(),
            $query->getPermissionMetadataId()
        );
    }

    /** @retrun ResourceKind[] */
    private function convertResourceKindIdsToResourceKinds(array $resourceKindIds): array {
        return array_map(
            function ($resourceKindOrId) {
                return $resourceKindOrId instanceof ResourceKind
                    ? $resourceKindOrId
                    : $this->resourceKindRepository->findByNameOrId($resourceKindOrId);
            },
            $resourceKindIds
        );
    }

    private function adjustContents(array $contentsFilters) {
        return array_map(
            function (ResourceContents $contentsFilter) {
                return $contentsFilter
                    ->withMetadataNamesMappedToIds($this->metadataRepository)
                    ->mapAllValues(
                        function (MetadataValue $value, int $metadataId) {
                            $metadata = $this->metadataRepository->findOne($metadataId);
                            try {
                                return $this->metadataValueAdjuster->adjustMetadataValue($value, $metadata);
                            } catch (\Exception $e) {
                                // we accept malformed values for filters, e.g. date: 2019-.*
                                return $value;
                            }
                        }
                    );
            },
            $contentsFilters
        );
    }

    public function convertSortByMetadataColumnsToIntegers(array $sortByIds): array {
        return array_map(
            function ($sortBy) {
                $sortId = is_numeric($sortBy['columnId']) ? intval($sortBy['columnId']) : $sortBy['columnId'];
                if (!is_numeric($sortId) && !in_array($sortId, ['id', 'kindId'])) {
                    $sortId = $this->metadataRepository->findByName($sortId)->getId();
                }
                return ['columnId' => $sortId, 'direction' => $sortBy['direction']];
            },
            $sortByIds
        );
    }
}
