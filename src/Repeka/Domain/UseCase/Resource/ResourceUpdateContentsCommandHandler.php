<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Constants\SystemTransition;
use Repeka\Domain\Cqrs\CommandBus;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Upload\ResourceFileHelper;

class ResourceUpdateContentsCommandHandler {
    /** @var ResourceFileHelper */
    private $fileHelper;
    /** @var CommandBus */
    private $commandBus;

    public function __construct(ResourceFileHelper $fileHelper, CommandBus $commandBus) {
        $this->fileHelper = $fileHelper;
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
