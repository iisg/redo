<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Constants\SystemTransition;
use Repeka\Domain\Cqrs\CommandBus;
use Repeka\Domain\Entity\ResourceEntity;

class ResourceCloneCommandHandler {
    /** @var CommandBus */
    private $commandBus;

    public function __construct(CommandBus $commandBus) {
        $this->commandBus = $commandBus;
    }

    public function handle(ResourceCloneCommand $command): ResourceEntity {
        $resourceToClone = $command->getResource();
        $resourceToBeAdded = new ResourceEntity($command->getKind(), $resourceToClone->getContents());
        if ($marking = $resourceToClone->getMarking()) {
            $resourceToBeAdded->setMarking($marking);
        }
        $resource = $this->commandBus->handle(
            new ResourceTransitionCommand(
                $resourceToBeAdded,
                $command->getContents(),
                SystemTransition::UPDATE()->toTransition($resourceToClone->getKind(), $resourceToClone),
                $command->getExecutor()
            )
        );
        return $resource;
    }
}
