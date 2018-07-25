<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Constants\SystemTransition;
use Repeka\Domain\Cqrs\CommandBus;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\ResourceRepository;

class ResourceCreateCommandHandler {
    /** @var CommandBus */
    private $commandBus;
    /** @var ResourceRepository */
    private $resourceRepository;

    public function __construct(ResourceRepository $resourceRepository, CommandBus $commandBus) {
        $this->resourceRepository = $resourceRepository;
        $this->commandBus = $commandBus;
    }

    public function handle(ResourceCreateCommand $command): ResourceEntity {
        $resourceBeforeAdd = new ResourceEntity($command->getKind(), ResourceContents::empty());
        $resourceToBeAdded = new ResourceEntity($command->getKind(), $command->getContents());
        if ($resourceToBeAdded->hasParent() && $resourceToBeAdded->hasWorkflow()) {
            $this->fillCopiedLockedMetadataFromParent($resourceToBeAdded, $resourceBeforeAdd);
        }
        return $this->commandBus->handle(
            new ResourceTransitionCommand(
                $resourceBeforeAdd,
                $resourceToBeAdded->getContents(),
                SystemTransition::CREATE()->toTransition($command->getKind()),
                $command->getExecutor()
            )
        );
    }

    private function fillCopiedLockedMetadataFromParent(ResourceEntity &$resourceToBeAdded, ResourceEntity &$resourceBeforeAdd): void {
        $parentId = $resourceToBeAdded->getParentId();
        $lockedInTheFirstPlaceMetadataIds = $resourceToBeAdded->getWorkflow()
            ->getInitialPlace()
            ->restrictingMetadataIds()
            ->locked()
            ->assignees()
            ->autoAssign()
            ->existingInResourceKind($resourceToBeAdded->getKind())
            ->get();
        foreach ($lockedInTheFirstPlaceMetadataIds as $metadataId) {
            if ($resourceToBeAdded->getKind()->getMetadataById($metadataId)->isCopiedToChildResource()) {
                $parentMetadataValues = $this->resourceRepository->findOne($parentId)->getValues($metadataId);
                $resourceBeforeAdd->updateContents(
                    $resourceBeforeAdd->getContents()->withReplacedValues($metadataId, $parentMetadataValues)
                );
                $resourceToBeAdded->updateContents(
                    $resourceToBeAdded->getContents()->withReplacedValues($metadataId, $parentMetadataValues)
                );
            }
        }
    }
}
