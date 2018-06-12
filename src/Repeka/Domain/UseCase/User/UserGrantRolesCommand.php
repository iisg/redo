<?php
namespace Repeka\Domain\UseCase\User;

use Repeka\Domain\Constants\SystemRole;
use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\NonValidatedCommand;
use Repeka\Domain\Entity\User;

class UserGrantRolesCommand extends AbstractCommand implements NonValidatedCommand {
    /** @var User */
    private $user;

    public function __construct(User $user) {
        $this->user = $user;
    }

    public function getUser(): User {
        return $this->user;
    }

    public function getRequiredRole(): ?SystemRole {
        return null;
    }
}
