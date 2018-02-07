<?php
namespace Repeka\Domain\UseCase\UserRole;

use Repeka\Domain\Cqrs\AbstractCommand;

class UserRoleCreateCommand extends AbstractCommand {
    protected $name;

    public function __construct(array $name) {
        $this->name = $name;
    }

    public function getName(): array {
        return $this->name;
    }
}
