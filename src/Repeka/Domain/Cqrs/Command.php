<?php
namespace Repeka\Domain\Cqrs;

use Repeka\Domain\Constants\SystemRole;
use Repeka\Domain\Entity\User;

interface Command {
    public function getCommandName();

    /** Allows to override Command's class name to fools the CommandBus and use different commands with the same handler. */
    public function getCommandClassName();

    public function getExecutor(): ?User;

    public function getRequiredRole(): ?SystemRole;
}
