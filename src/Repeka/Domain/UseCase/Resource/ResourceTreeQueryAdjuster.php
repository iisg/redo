<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandAdjuster;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceKindRepository;

class ResourceTreeQueryAdjuster implements CommandAdjuster {

    /** @var MetadataRepository */
    private $metadataRepository;
    /** @var ResourceKindRepository */
    private $resourceKindRepository;

    public function __construct(MetadataRepository $metadataRepository, ResourceKindRepository $resourceKindRepository) {
        $this->metadataRepository = $metadataRepository;
        $this->resourceKindRepository = $resourceKindRepository;
    }

    /**
     * @param ResourceTreeQuery $query
     * @return ResourceTreeQuery
     */
    public function adjustCommand(Command $query): Command {
        return ResourceTreeQuery::withParams(
            $query->getRootId(),
            $query->getDepth(),
            $query->getSiblings(),
            $query->getResourceClasses(),
            $this->convertResourceKindIdsToResourceKinds($query->getResourceKinds()),
            $this->mapMetadataNamesToIdsInContentFilter($query->getContentsFilter()),
            $query->getPage(),
            $query->getResultsPerPage(),
            $query->oneMoreElements()
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

    private function mapMetadataNamesToIdsInContentFilter(ResourceContents $contentsFilter) {
        return $contentsFilter->withMetadataNamesMappedToIds($this->metadataRepository);
    }
}
