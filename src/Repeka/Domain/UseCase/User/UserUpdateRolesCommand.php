<?php
namespace Repeka\Domain\UseCase\User;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Entity\User;

class UserUpdateRolesCommand extends AbstractCommand {
    /** @var User */
    private $user;
    private $roles;
    /** @var User */
    private $executor;

    public function __construct(User $user, array $roles, User $executor) {
        $this->user = $user;
        $this->roles = $roles;
        $this->executor = $executor;
    }

    public function getUser(): User {
        return $this->user;
    }

    public function getRoles(): array {
        return $this->roles;
    }

    public function getExecutor(): User {
        return $this->executor;
    }
}
