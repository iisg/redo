<?php
namespace Repeka\Domain\Cqrs;

use Repeka\Domain\Constants\SystemRole;
use Repeka\Domain\Entity\User;

interface Command {
    public function getCommandName();

    public function getExecutor(): ?User;

    public function getRequiredRole(): ?SystemRole;
}
