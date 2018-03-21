<?php
namespace Repeka\Domain\UseCase\User;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\AuditedCommand;
use Repeka\Domain\Cqrs\NonValidatedCommand;

/**
 * This command does not authenticate user. It only audits the authentication attempt.
 * Authentication is a responsibility of the Application layer.
 */
class UserAuthenticateCommand extends AbstractCommand implements NonValidatedCommand, AuditedCommand {
    /** @var string */
    private $username;
    /** @var bool */
    private $successful;

    /** @SuppressWarnings("PHPMD.BooleanArgumentFlag") */
    public function __construct(string $username, bool $successful = true) {
        $this->username = $username;
        $this->successful = $successful;
    }

    public function getUsername(): string {
        return $this->username;
    }

    public function isSuccessful() {
        return $this->successful;
    }
}
