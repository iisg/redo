<?php
namespace Repeka\Domain\UseCase\UserRole;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\UserRole;

class UserRoleDeleteCommand extends Command {
    /** @var UserRole */
    private $userRole;

    public function __construct(UserRole $userRole) {
        $this->userRole = $userRole;
    }

    public function getUserRole(): UserRole {
        return $this->userRole;
    }
}
