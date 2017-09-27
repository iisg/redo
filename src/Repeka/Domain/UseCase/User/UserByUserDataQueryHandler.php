<?php
namespace Repeka\Domain\UseCase\User;

use Repeka\Domain\Repository\UserRepository;

class UserByUserDataQueryHandler {
    /** @var UserRepository */
    private $userRepository;

    public function __construct(UserRepository $userRepository) {
        $this->userRepository = $userRepository;
    }

    public function handle(UserByUserDataQuery $query) {
        return $this->userRepository->findByUserData($query->getUserData());
    }
}
