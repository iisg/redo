<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Cqrs\CommandBus;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Utils\EntityUtils;

class ResourceMultipleCloneCommandHandler {
    /** @var CommandBus */
    private $commandBus;

    public function __construct(CommandBus $commandBus) {
        $this->commandBus = $commandBus;
    }

    public function handle(ResourceMultipleCloneCommand $command) {
        $cloneTimes = $command->getCloneTimes();
        $contents = $this->stripValuesForUniqueMetadata($command->getResource()->getKind(), $command->getContents());
        foreach (range(1, $cloneTimes) as $number) {
            $labelMetadataValue = $contents->getValues(SystemMetadata::RESOURCE_LABEL)[0];
            $newContents = $contents->withReplacedValues(
                SystemMetadata::RESOURCE_LABEL,
                $labelMetadataValue->getValue() . ' - clone ' . $number
            );
            $this->commandBus->handle(
                new ResourceCloneCommand($command->getKind(), $command->getResource(), $newContents, $command->getExecutor())
            );
        }
    }

    private function stripValuesForUniqueMetadata(ResourceKind $resourceKind, ResourceContents $contents): ResourceContents {
        $metadata = array_filter(
            $resourceKind->getMetadataList(),
            function (Metadata $metadata) {
                $constraints = $metadata->getConstraints();
                return $constraints['uniqueInResourceClass'] ?? false;
            }
        );
        $metadataIds = EntityUtils::mapToIds($metadata);
        $contentsArray = $contents->toArray();
        foreach ($metadataIds as $id) {
            unset($contentsArray[$id]);
        }
        return ResourceContents::fromArray($contentsArray);
    }
}
