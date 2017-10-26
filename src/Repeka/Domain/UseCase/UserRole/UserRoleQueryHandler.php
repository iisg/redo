<?php
namespace Repeka\Domain\UseCase\UserRole;

use Repeka\Domain\Entity\UserRole;
use Repeka\Domain\Repository\UserRepository;
use Repeka\Domain\Repository\UserRoleRepository;

class UserRoleQueryHandler {
    /**
     * @var UserRepository
     */
    private $userRoleRepository;

    public function __construct(UserRoleRepository $userRoleRepository) {
        $this->userRoleRepository = $userRoleRepository;
    }

    public function handle(UserRoleQuery $query): UserRole {
        return $this->userRoleRepository->findOne($query->getId());
    }
}
