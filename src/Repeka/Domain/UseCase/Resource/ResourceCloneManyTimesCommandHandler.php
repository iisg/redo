<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\CommandBus;
use Repeka\Domain\Entity\ResourceEntity;

class ResourceCloneManyTimesCommandHandler {
    /** @var CommandBus */
    private $commandBus;

    public function __construct(CommandBus $commandBus) {
        $this->commandBus = $commandBus;
    }

    public function handle(ResourceCloneManyTimesCommand $command): ResourceEntity {
        $cloneTimes = $command->getCloneTimes();
        for ($i = 0; $i < $cloneTimes; $i++) {
            $cloned = $this->commandBus->handle(new ResourceCloneCommand($command->getResource(), $command->getExecutor()));
        }
        return $cloned;
    }
}
