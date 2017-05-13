<?php
namespace Repeka\Domain\UseCase\User;

use Repeka\Domain\Cqrs\Command;

class UserCreateCommand extends Command {
    /** @var string */
    private $username;
    /** @var null|string */
    private $plainPassword;
    /** @var null|string */
    private $email;
    /** @var array */
    private $userData;

    public function __construct(string $username, string $plainPassword = null, string $email = null, array $userData = []) {
        $this->username = $username;
        $this->plainPassword = $plainPassword;
        $this->email = $email;
        $this->userData = $userData;
    }

    public function getUsername(): string {
        return $this->username;
    }

    public function getUserData(): array {
        return $this->userData;
    }

    public function getPlainPassword(): ?string {
        return $this->plainPassword;
    }

    public function getEmail() {
        return $this->email;
    }
}
