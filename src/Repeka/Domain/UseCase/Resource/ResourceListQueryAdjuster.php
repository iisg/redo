<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandAdjuster;
use Repeka\Domain\Repository\MetadataRepository;

class ResourceListQueryAdjuster implements CommandAdjuster {

    /** @var MetadataRepository */
    private $metadataRepository;

    public function __construct(MetadataRepository $metadataRepository) {
        $this->metadataRepository = $metadataRepository;
    }

    /**
     * @param ResourceListQuery $query
     * @return ResourceListQuery
     */
    public function adjustCommand(Command $query): Command {
        return ResourceListQuery::withParams(
            $query->getIds(),
            $query->getResourceClasses(),
            $query->getResourceKinds(),
            $query->getSortByMetadataIds(),
            $query->getParentId(),
            $query->getContentsFilter()->withMetadataNamesMappedToIds($this->metadataRepository),
            $query->onlyTopLevel(),
            $query->getPage(),
            $query->getResultsPerPage(),
            $query->getWorkflowPlacesIds()
        );
    }
}
