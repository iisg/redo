<?php
namespace Repeka\Domain\UseCase\User;

use Repeka\Domain\Cqrs\AbstractCommandAuditor;
use Repeka\Domain\Cqrs\Command;

class UserAuthenticateCommandAuditor extends AbstractCommandAuditor {
    public function afterHandling(Command $command, $result): ?array {
        return ['username' => $command->getUsername()];
    }

    public function afterError(Command $command, \Exception $exception): ?array {
        return ['username' => $command->getUsername()];
    }
}
