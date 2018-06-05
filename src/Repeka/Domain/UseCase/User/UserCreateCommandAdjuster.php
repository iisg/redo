<?php
namespace Repeka\Domain\UseCase\User;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandAdjuster;

class UserCreateCommandAdjuster implements CommandAdjuster {
    /** @param UserCreateCommand $command */
    public function adjustCommand(Command $command): Command {
        return new UserCreateCommand(
            $this->normalizeUsername($command->getUsername()),
            $command->getPlainPassword(),
            $command->getUserData()
        );
    }

    public static function normalizeUsername(string $username) {
        $username = preg_replace('#[^a-z0-9_\./-]#i', '', $username);
        return strtolower($username);
    }
}
