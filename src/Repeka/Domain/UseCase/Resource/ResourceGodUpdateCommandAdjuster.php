<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandAdjuster;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceKindRepository;

class ResourceGodUpdateCommandAdjuster implements CommandAdjuster {
    /** @var ResourceKindRepository */
    private $resourceKindRepository;
    /** @var MetadataRepository */
    private $metadataRepository;

    public function __construct(ResourceKindRepository $resourceKindRepository, MetadataRepository $metadataRepository) {
        $this->resourceKindRepository = $resourceKindRepository;
        $this->metadataRepository = $metadataRepository;
    }

    /**
     * @param ResourceGodUpdateCommand $command
     * @return ResourceGodUpdateCommand
     */
    public function adjustCommand(Command $command): Command {
        return ResourceGodUpdateCommand::withParams(
            $command->getResource(),
            $command->getContents()->withMetadataNamesMappedToIds($this->metadataRepository),
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
