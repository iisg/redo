<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandAdjuster;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceKindRepository;

class ResourceListQueryAdjuster implements CommandAdjuster {

    /** @var MetadataRepository */
    private $metadataRepository;
    /** @var ResourceKindRepository */
    private $resourceKindRepository;

    public function __construct(MetadataRepository $metadataRepository, ResourceKindRepository $resourceKindRepository) {
        $this->metadataRepository = $metadataRepository;
        $this->resourceKindRepository = $resourceKindRepository;
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
            $this->mapMetadataIdsToNamesInContentFilter($query->getContentsFilters()),
            $query->onlyTopLevel(),
            $query->getPage(),
            $query->getResultsPerPage(),
            $query->getWorkflowPlacesIds()
        );
    }

    /** @retrun ResourceKind[] */
    private function convertResourceKindIdsToResourceKinds(array $resourceKindIds): array {
        return array_map(
            function ($resourceKindOrId) {
                return $resourceKindOrId instanceof ResourceKind
                    ? $resourceKindOrId
                    : $this->resourceKindRepository->findOne($resourceKindOrId);
            },
            $resourceKindIds
        );
    }

    private function convertSortByMetadataColumnsToIntegers(array $sortByIds): array {
        return array_map(
            function ($sortBy) {
                $sortId = is_numeric($sortBy['columnId']) ? intval($sortBy['columnId']) : $sortBy['columnId'];
                return ['columnId' => $sortId, 'direction' => $sortBy['direction']];
            },
            $sortByIds
        );
    }

    private function mapMetadataIdsToNamesInContentFilter(array $contentsFilters) {
        return array_map(
            function (ResourceContents $contentsFilter) {
                return $contentsFilter->withMetadataNamesMappedToIds($this->metadataRepository);
            },
            $contentsFilters
        );
    }
}
