<?php
namespace Repeka\Domain\UseCase\UserRole;

use Repeka\Domain\Entity\UserRole;
use Repeka\Domain\Repository\ResourceWorkflowRepository;
use Repeka\Domain\Repository\UserRoleRepository;

class UserRoleCreateCommandHandler {
    /** @var ResourceWorkflowRepository */
    private $userRoleRepository;

    public function __construct(UserRoleRepository $userRoleRepository) {
        $this->userRoleRepository = $userRoleRepository;
    }

    public function handle(UserRoleCreateCommand $command): UserRole {
        $userRole = new UserRole($command->getName());
        return $this->userRoleRepository->save($userRole);
    }
}
