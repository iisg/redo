<?php
namespace Repeka\Domain\UseCase\User;

use Repeka\Domain\Entity\User;
use Repeka\Domain\Repository\UserRepository;

class UserListQueryHandler {
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(UserRepository $userRepository) {
        $this->userRepository = $userRepository;
    }

    /**
     * @return User[]
     */
    public function handle(): array {
        return $this->userRepository->findAll();
    }
}
