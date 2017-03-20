<?php
namespace Repeka\Domain\UseCase\UserRole;

use Repeka\Domain\Entity\UserRole;

class UserRoleUpdateCommand extends UserRoleCreateCommand {
    private $role;

    public function __construct(UserRole $role, array $name) {
        parent::__construct($name);
        $this->role = $role;
    }

    public function getRole(): UserRole {
        return $this->role;
    }
}
