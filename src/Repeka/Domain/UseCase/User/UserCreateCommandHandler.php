<?php
namespace Repeka\Domain\UseCase\User;

use Repeka\Domain\Entity\User;
use Repeka\Domain\Factory\UserFactory;
use Repeka\Domain\Repository\UserRepository;

class UserCreateCommandHandler {
    /** @var UserRepository */
    private $userRepository;
    /** @var UserFactory */
    private $userFactory;

    public function __construct(UserRepository $userRepository, UserFactory $userFactory) {
        $this->userRepository = $userRepository;
        $this->userFactory = $userFactory;
    }

    public function handle(UserCreateCommand $command): User {
        $user = $this->userFactory->createUser(
            $command->getUsername(),
            $command->getPlainPassword(),
            $command->getUserData()
        );
        $this->userRepository->save($user);
        return $user;
    }
}
