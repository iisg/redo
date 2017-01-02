<?php
namespace Repeka\Domain\UseCase\User;

use Repeka\Domain\Cqrs\Command;

class UserUpdateStaticPermissionsCommand extends Command {
    private $userId;
    private $permissions;

    public function __construct(int $userId, array $permissions) {
        $this->userId = $userId;
        $this->permissions = $permissions;
    }

    public function getUserId(): int {
        return $this->userId;
    }

    public function getPermissions(): array {
        return $this->permissions;
    }
}
