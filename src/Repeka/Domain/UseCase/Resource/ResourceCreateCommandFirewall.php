<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandFirewall;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Exception\InsufficientPrivilegesException;
use Repeka\Domain\Repository\ResourceRepository;

class ResourceCreateCommandFirewall implements CommandFirewall {
    /** @var ResourceRepository */
    private $resourceRepository;

    public function __construct(ResourceRepository $resourceRepository) {
        $this->resourceRepository = $resourceRepository;
    }

    /** @param ResourceCreateCommand $command */
    public function ensureCanExecute(Command $command, User $executor): void {
        if (!$command->isTopLevel()) {
            $parentId = current($command->getContents()->getValues(SystemMetadata::PARENT));
            $parentResource = $this->resourceRepository->findOne($parentId);
            $allowedReproductors = $parentResource->getContents()->getValues(SystemMetadata::REPRODUCTOR);
            if (!$executor->belongsToAnyOfGivenUserGroupsIds($allowedReproductors)) {
                throw new InsufficientPrivilegesException(
                    'The executor is not a reproductor of the parent resource. Allowed: ' . implode(', ', $allowedReproductors)
                );
            }
        }
    }
}
