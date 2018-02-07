<?php
namespace Repeka\Domain\UseCase\UserRole;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\NonValidatedCommand;

class UserRoleQuery extends AbstractCommand implements NonValidatedCommand {
    /** @var int */
    private $id;

    public function __construct(int $id) {
        $this->id = $id;
    }

    public function getId(): int {
        return $this->id;
    }
}
