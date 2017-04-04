<?php
namespace Repeka\Domain\UseCase\User;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\User;

class UserUpdateRolesCommand extends Command {
    /** @var User */
    private $user;
    private $roles;

    public function __construct(User $user, array $roles) {
        $this->user = $user;
        $this->roles = $roles;
    }

    public function getUser(): User {
        return $this->user;
    }

    public function getRoles(): array {
        return $this->roles;
    }
}
