<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandAdjuster;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\Repository\ResourceRepository;

class ResourceMultipleCloneCommandAdjuster implements CommandAdjuster {
    /** @var ResourceRepository */
    private $resourceRepository;

    public function __construct(ResourceRepository $resourceRepository) {
        $this->resourceRepository = $resourceRepository;
    }

    /**
     * @param ResourceMultipleCloneCommand $command
     * @return ResourceCloneCommand
     */
    public function adjustCommand(Command $command): Command {
        return new ResourceMultipleCloneCommand(
            $command->getKind(),
            $this->convertIdToResource($command->getResource()),
            $command->getContents(),
            $command->getCloneTimes(),
            $command->getExecutor()
        );
    }

    private function convertIdToResource($idOrResource): ?ResourceEntity {
        if ($idOrResource === null || $idOrResource instanceof ResourceEntity) {
            return $idOrResource;
        } else {
            try {
                return $this->resourceRepository->findOne($idOrResource);
            } catch (EntityNotFoundException $e) {
                return null;
            }
        }
    }
}
