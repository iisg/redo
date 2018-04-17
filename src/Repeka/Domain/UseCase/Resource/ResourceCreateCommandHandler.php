<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Constants\SystemTransition;
use Repeka\Domain\Cqrs\CommandBus;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;

class ResourceCreateCommandHandler {
    /** @var CommandBus */
    private $commandBus;

    public function __construct(CommandBus $commandBus) {
        $this->commandBus = $commandBus;
    }

    public function handle(ResourceCreateCommand $command): ResourceEntity {
        $resource = new ResourceEntity($command->getKind(), ResourceContents::empty());
        $resource = $this->commandBus->handle(
            new ResourceTransitionCommand(
                $resource,
                $command->getContents(),
                SystemTransition::CREATE()->toTransition($command->getKind()),
                $command->getExecutor()
            )
        );
        return $resource;
    }
}
