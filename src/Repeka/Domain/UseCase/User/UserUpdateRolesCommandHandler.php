<?php
namespace Repeka\Domain\UseCase\User;

use Repeka\Domain\Entity\User;
use Repeka\Domain\Entity\UserRole;
use Repeka\Domain\Repository\UserRepository;
use Repeka\Domain\Repository\UserRoleRepository;

class UserUpdateRolesCommandHandler {
    /** @var UserRepository */
    private $userRepository;
    private $systemRoles;

    public function __construct(UserRepository $userRepository, UserRoleRepository $userRoleRepository) {
        $this->userRepository = $userRepository;
        $this->prepareSystemRolesMap($userRoleRepository);
    }

    public function handle(UserUpdateRolesCommand $command): User {
        $user = $command->getUser();
        $roles = $this->getRolesWithImplications($command->getRoles());
        $user->updateRoles($roles);
        return $this->userRepository->save($user);
    }

    private function prepareSystemRolesMap(UserRoleRepository $userRoleRepository) {
        foreach ($userRoleRepository->findSystemRoles() as $systemRole) {
            $this->systemRoles[$systemRole->getId()] = $systemRole;
        }
    }

    /**
     * @param UserRole[] $roles
     * @return UserRole[]
     */
    private function getRolesWithImplications(array $roles): array {
        foreach ($roles as $role) {
            if ($role->isSystemRole()) {
                foreach ($role->toSystemRole()->getImpliedRoles() as $impliedSystemRole) {
                    $impliedRole = $this->systemRoles[$impliedSystemRole->getValue()];
                    if (!in_array($impliedRole, $roles)) {
                        $roles[] = $impliedRole;
                    }
                }
            }
        }
        return $roles;
    }
}
