<?php
namespace Repeka\Domain\UseCase\UserRole;

use Repeka\Domain\Repository\UserRoleRepository;

class UserRoleDeleteCommandHandler {
    /** @var UserRoleRepository */
    private $userRoleRepository;

    public function __construct(UserRoleRepository $userRoleRepository) {
        $this->userRoleRepository = $userRoleRepository;
    }

    public function handle(UserRoleDeleteCommand $command): void {
        $this->userRoleRepository->delete($command->getUserRole());
    }
}
