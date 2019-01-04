<?php
namespace Repeka\Domain\Cqrs;

use Repeka\Domain\Constants\SystemRole;

trait RequireNoRoles {
    public function getRequiredRole(): ?SystemRole {
        return null;
    }
}
