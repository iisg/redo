<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandAdjuster;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Metadata\MetadataValueAdjuster\ResourceContentsAdjuster;
use Repeka\Domain\Repository\ResourceKindRepository;

class ResourceGodUpdateCommandAdjuster implements CommandAdjuster {
    /** @var ResourceKindRepository */
    private $resourceKindRepository;
    /** @var ResourceContentsAdjuster */
    private $resourceContentsAdjuster;

    public function __construct(ResourceKindRepository $resourceKindRepository, ResourceContentsAdjuster $resourceContentsAdjuster) {
        $this->resourceKindRepository = $resourceKindRepository;
        $this->resourceContentsAdjuster = $resourceContentsAdjuster;
    }

    /**
     * @param ResourceGodUpdateCommand $command
     * @return ResourceGodUpdateCommand
     */
    public function adjustCommand(Command $command): Command {
        return new ResourceGodUpdateCommand(
            $command->getResource(),
            $this->resourceContentsAdjuster->adjust($command->getContents()),
            $this->convertResourceKindIdToResourceKind($command->getResourceKind()),
            $command->getPlacesIds()
        );
    }

    private function convertResourceKindIdToResourceKind($resourceKindOrId): ?ResourceKind {
        return $resourceKindOrId == null || $resourceKindOrId instanceof ResourceKind
            ? $resourceKindOrId
            : $this->resourceKindRepository->findOne($resourceKindOrId);
    }
}
