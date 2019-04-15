<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Cqrs\CommandBus;

class ResourceMultipleCloneCommandHandler {
    /** @var CommandBus */
    private $commandBus;

    public function __construct(CommandBus $commandBus) {
        $this->commandBus = $commandBus;
    }

    public function handle(ResourceMultipleCloneCommand $command) {
        $cloneTimes = $command->getCloneTimes();
        foreach (range(1, $cloneTimes) as $number) {
            $labelMetadataValue = $command->getContents()->getValues(SystemMetadata::RESOURCE_LABEL)[0];
            $newContents = $command->getContents()->withReplacedValues(
                SystemMetadata::RESOURCE_LABEL,
                $labelMetadataValue->getValue() . ' - clone ' . $number
            );
            $this->commandBus->handle(
                new ResourceCloneCommand($command->getKind(), $command->getResource(), $newContents, $command->getExecutor())
            );
        }
    }
}
