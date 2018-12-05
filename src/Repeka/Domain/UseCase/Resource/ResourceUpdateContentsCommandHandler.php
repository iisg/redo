<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Constants\SystemTransition;
use Repeka\Domain\Cqrs\CommandBus;
use Repeka\Domain\Entity\ResourceEntity;

class ResourceUpdateContentsCommandHandler {
    /** @var CommandBus */
    private $commandBus;

    public function __construct(CommandBus $commandBus) {
        $this->commandBus = $commandBus;
    }

    public function handle(ResourceUpdateContentsCommand $command): ResourceEntity {
        $resource = $command->getResource();
        $resource = $this->commandBus->handle(
            new ResourceTransitionCommand(
                $resource,
                $command->getContents(),
                SystemTransition::UPDATE()->toTransition($resource->getKind(), $resource),
                $command->getExecutor()
            )
        );
        return $resource;
    }
}
