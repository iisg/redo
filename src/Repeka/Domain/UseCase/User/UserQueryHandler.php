<?php
namespace Repeka\Domain\UseCase\User;

use Repeka\Domain\Entity\User;
use Repeka\Domain\Repository\UserRepository;

class UserQueryHandler {
    /** @var UserRepository */
    private $userRepository;

    public function __construct(UserRepository $userRepository) {
        $this->userRepository = $userRepository;
    }

    public function handle(UserQuery $query): User {
        return $this->userRepository->findOne($query->getId());
    }
}
