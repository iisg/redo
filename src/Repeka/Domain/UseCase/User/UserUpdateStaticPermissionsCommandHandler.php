<?php
namespace Repeka\Domain\UseCase\User;

use Repeka\Domain\Entity\User;
use Repeka\Domain\Repository\UserRepository;

class UserUpdateStaticPermissionsCommandHandler {
    /** @var UserRepository */
    private $userRepository;

    public function __construct(UserRepository $userRepository) {
        $this->userRepository = $userRepository;
    }

    public function handle(UserUpdateStaticPermissionsCommand $command): User {
        $user = $this->userRepository->findOne($command->getUserId());
        $user->updateStaticPermissions($command->getPermissions());
        return $this->userRepository->save($user);
    }
}
