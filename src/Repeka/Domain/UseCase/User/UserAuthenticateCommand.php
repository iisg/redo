<?php
namespace Repeka\Domain\UseCase\User;

use Repeka\Domain\Constants\SystemRole;
use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\AuditedCommand;
use Repeka\Domain\Cqrs\NonValidatedCommand;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This command does not authenticate user. It only audits the authentication attempt.
 * Authentication is a responsibility of the Application layer.
 */
class UserAuthenticateCommand extends AbstractCommand implements NonValidatedCommand, AuditedCommand {
    /** @var string */
    private $username;
    /** @var string */
    private $addressIp;
    /** @var bool */
    private $successful;

    /** @SuppressWarnings("PHPMD.BooleanArgumentFlag") */
    public function __construct(string $username, ContainerInterface $container, bool $successful = true) {
        $this->username = $username;
        $request = $container->get('request_stack')->getCurrentRequest();
        $this->addressIp = $request ? $request->getClientIp() : '';
        $this->successful = $successful;
    }

    public function getUsername(): string {
        return $this->username;
    }

    public function getAddressIp(): string {
        return $this->addressIp;
    }

    public function isSuccessful() {
        return $this->successful;
    }

    public function getRequiredRole(): ?SystemRole {
        return null;
    }
}
