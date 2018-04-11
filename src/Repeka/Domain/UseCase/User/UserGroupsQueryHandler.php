<?php
namespace Repeka\Domain\UseCase\User;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\UserRepository;

class UserGroupsQueryHandler {
    /** @var UserRepository */
    private $userRepository;

    public function __construct(UserRepository $userRepository) {
        $this->userRepository = $userRepository;
    }

    /** @return ResourceEntity[] */
    public function handle(UserGroupsQuery $query): array {
        return $this->userRepository->findUserGroups($query->getUser());
    }
}
