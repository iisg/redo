<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandAdjuster;
use Repeka\Domain\Entity\MetadataValue;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Metadata\MetadataValueAdjuster\MetadataValueAdjusterComposite;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceKindRepository;

class ResourceGodUpdateCommandAdjuster implements CommandAdjuster {
    /** @var ResourceKindRepository */
    private $resourceKindRepository;
    /** @var MetadataRepository */
    private $metadataRepository;
    /** @var MetadataValueAdjusterComposite */
    private $metadataValueAdjuster;

    public function __construct(
        ResourceKindRepository $resourceKindRepository,
        MetadataRepository $metadataRepository,
        MetadataValueAdjusterComposite $metadataValueAdjuster
    ) {
        $this->resourceKindRepository = $resourceKindRepository;
        $this->metadataRepository = $metadataRepository;
        $this->metadataValueAdjuster = $metadataValueAdjuster;
    }

    /**
     * @param ResourceGodUpdateCommand $command
     * @return ResourceGodUpdateCommand
     */
    public function adjustCommand(Command $command): Command {
        $newContents = $command->getContents();
        $newContents = $newContents->withMetadataNamesMappedToIds($this->metadataRepository);
        $newContents = $newContents->mapAllValues([$this, 'adjustResourceContents']);
        return ResourceGodUpdateCommand::withParams(
            $command->getResource(),
            $newContents,
            $this->convertResourceKindIdToResourceKind($command->getResourceKind()),
            $command->getPlacesIds()
        );
    }

    public function adjustResourceContents(MetadataValue $value, int $metadataId) {
        $metadata = $this->metadataRepository->findOne($metadataId);
        return $this->metadataValueAdjuster->adjustMetadataValue($value, $metadata->getControl());
    }

    private function convertResourceKindIdToResourceKind($resourceKindOrId): ?ResourceKind {
        return $resourceKindOrId == null || $resourceKindOrId instanceof ResourceKind
            ? $resourceKindOrId
            : $this->resourceKindRepository->findOne($resourceKindOrId);
    }
}
