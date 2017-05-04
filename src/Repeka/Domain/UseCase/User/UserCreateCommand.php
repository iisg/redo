<?php
namespace Repeka\Domain\UseCase\User;

use Repeka\Domain\Cqrs\Command;

class UserCreateCommand extends Command {
    /** @var string */
    private $username;

    public function __construct(string $username) {
        $this->username = $username;
    }

    public function getUsername(): string {
        return $this->username;
    }
}
