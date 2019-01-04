<?php
namespace Repeka\Application\Cqrs\Middleware;

use Repeka\Application\Service\CurrentUserAware;
use Repeka\Domain\Constants\SystemResource;
use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandFirewall;
use Repeka\Domain\Cqrs\FirewalledCommand;
use Repeka\Domain\Entity\HasResourceClass;
use Repeka\Domain\Exception\InsufficientPrivilegesException;
use Repeka\Domain\Utils\EntityUtils;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FirewallMiddleware implements CommandBusMiddleware {
    use CurrentUserAware;

    private static $firewallEnabled = true;

    /** @var ContainerInterface */
    private $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function handle(Command $command, callable $next) {
        if (self::$firewallEnabled) {
            $this->setExecutor($command);
            $this->ensureHasRequiredRole($command);
            $this->ensureCommandFirewallAllows($command);
        }
        return $next($command);
    }

    private function ensureHasRequiredRole(Command $command): void {
        $requiredRole = $command->getRequiredRole();
        if ($requiredRole) {
            $executor = $command->getExecutor();
            $requiredRoleName = $command instanceof HasResourceClass
                ? $requiredRole->roleName($command->getResourceClass())
                : $requiredRole->roleName();
            if (!$executor->hasRole($requiredRoleName)) {
                throw new InsufficientPrivilegesException(
                    "User {$executor->getUsername()} does not have required role {$requiredRoleName} "
                    . "to execute {$command->getCommandName()} command."
                );
            }
        }
    }

    private function ensureCommandFirewallAllows(Command $command): void {
        if ($command instanceof FirewalledCommand) {
            $firewallId = $this->getCommandFirewallId($command);
            if ($this->container->has($firewallId)) {
                /** @var CommandFirewall $firewall */
                $firewall = $this->container->get($firewallId);
                $firewall->ensureCanExecute($command, $command->getExecutor());
            } else {
                throw new \InvalidArgumentException(
                    "Could not find a firewall for the {$command->getCommandName()}. "
                    . "Looking for the {$firewallId}. "
                    . "If the command is not meant to have a custom firewall, it must not implement the FirwalledCommand."
                );
            }
        }
    }

    private function setExecutor(Command $command) {
        if (!$command->getExecutor()) {
            $executor = $this->getCurrentUser();
            if (!$executor) {
                $executor = SystemResource::UNAUTHENTICATED_USER()->toUser();
            }
            EntityUtils::forceSetField($command, $executor, 'executor');
        }
    }

    private function getCommandFirewallId(Command $command) {
        return $command->getCommandClassName() . 'Firewall';
    }

    public static function bypass(callable $callback) {
        $previous = self::$firewallEnabled;
        self::$firewallEnabled = false;
        try {
            $result = $callback();
        } finally {
            self::$firewallEnabled = $previous;
        }
        return $result;
    }
}
