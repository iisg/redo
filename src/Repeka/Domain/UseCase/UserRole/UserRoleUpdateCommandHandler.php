<?php
namespace Repeka\Domain\UseCase\UserRole;

use Repeka\Domain\Entity\UserRole;
use Repeka\Domain\Repository\ResourceWorkflowRepository;
use Repeka\Domain\Repository\UserRoleRepository;

class UserRoleUpdateCommandHandler {
    /** @var ResourceWorkflowRepository */
    private $userRoleRepository;

    public function __construct(UserRoleRepository $userRoleRepository) {
        $this->userRoleRepository = $userRoleRepository;
    }

    public function handle(UserRoleUpdateCommand $command): UserRole {
        $role = $command->getRole();
        $role->update($command->getName());
        return $this->userRoleRepository->save($role);
    }
}
