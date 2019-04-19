<?php
namespace Repeka\Domain\UseCase\User;

use Repeka\Domain\Entity\User;
use Repeka\Domain\Factory\UserFactory;
use Repeka\Domain\Repository\UserRepository;

class UserChangePasswordCommandHandler {
    /** @var UserRepository */
    private $userRepository;
    /** @var UserFactory */
    private $userFactory;

    public function __construct(UserRepository $userRepository, UserFactory $userFactory) {
        $this->userRepository = $userRepository;
        $this->userFactory = $userFactory;
    }

    public function handle(UserChangePasswordCommand $command): User {
        $user = $this->userFactory->updatePassword($command->getUser(), $command->getPlainPassword());
        $this->userRepository->save($user);
        return $user;
    }
}
