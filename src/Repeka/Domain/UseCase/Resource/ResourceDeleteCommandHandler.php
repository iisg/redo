<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Constants\SystemTransition;
use Repeka\Domain\Cqrs\CommandBus;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Repository\ResourceRepository;

class ResourceDeleteCommandHandler {
    /** @var ResourceRepository */
    private $resourceRepository;
    /** @var CommandBus */
    private $commandBus;

    public function __construct(ResourceRepository $resourceRepository, CommandBus $commandBus) {
        $this->resourceRepository = $resourceRepository;
        $this->commandBus = $commandBus;
    }

    public function handle(ResourceDeleteCommand $command): int {
        $resource = $command->getResource();
        $resourceId = $resource->getId();
        $resource = $this->commandBus->handle(
            new ResourceTransitionCommand(
                $resource,
                ResourceContents::empty(),
                SystemTransition::DELETE()->toTransition($resource->getKind(), $resource),
                $command->getExecutor()
            )
        );
        $this->resourceRepository->delete($resource);
        return $resourceId;
    }
}
