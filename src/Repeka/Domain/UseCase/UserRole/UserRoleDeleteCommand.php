<?php
namespace Repeka\Domain\UseCase\UserRole;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Entity\UserRole;

class UserRoleDeleteCommand extends AbstractCommand {
    /** @var UserRole */
    private $userRole;

    public function __construct(UserRole $userRole) {
        $this->userRole = $userRole;
    }

    public function getUserRole(): UserRole {
        return $this->userRole;
    }
}
