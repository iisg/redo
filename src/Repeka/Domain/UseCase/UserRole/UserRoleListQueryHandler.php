<?php
namespace Repeka\Domain\UseCase\UserRole;

use Repeka\Domain\Entity\UserRole;
use Repeka\Domain\Repository\UserRepository;
use Repeka\Domain\Repository\UserRoleRepository;

class UserRoleListQueryHandler {
    /**
     * @var UserRepository
     */
    private $userRoleRepository;

    public function __construct(UserRoleRepository $userRoleRepository) {
        $this->userRoleRepository = $userRoleRepository;
    }

    /**
     * @return UserRole[]
     */
    public function handle(): array {
        return $this->userRoleRepository->findAll();
    }
}
