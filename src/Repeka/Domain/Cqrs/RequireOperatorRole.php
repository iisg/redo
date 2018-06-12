<?php
namespace Repeka\Domain\Cqrs;

use Repeka\Domain\Constants\SystemRole;

trait RequireOperatorRole {
    public function getRequiredRole(): ?SystemRole {
        return SystemRole::OPERATOR();
    }
}
