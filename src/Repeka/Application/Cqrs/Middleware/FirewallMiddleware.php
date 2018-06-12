<?php
namespace Repeka\Application\Cqrs\Middleware;

use Repeka\Application\Service\CurrentUserAware;
use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\HasResourceClass;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Exception\InsufficientPrivilegesException;
use Repeka\Domain\Utils\EntityUtils;

class FirewallMiddleware implements CommandBusMiddleware {
    use CurrentUserAware;

    private static $firewallEnabled = true;

    public function handle(Command $command, callable $next) {
        if (self::$firewallEnabled) {
            $requiredRole = $command->getRequiredRole();
            if ($requiredRole) {
                $executor = $this->getExecutor($command);
                $requiredRoleName = $command instanceof HasResourceClass
                    ? $requiredRole->roleName($command->getResourceClass())
                    : $requiredRole->roleName();
                if (!in_array($requiredRoleName, $executor->getRoles())) {
                    throw new InsufficientPrivilegesException(
                        "User {$executor->getUsername()} does not have required role {$requiredRoleName} "
                        . "to execute {$command->getCommandName()} command."
                    );
                }
            }
        }
        return $next($command);
    }

    private function getExecutor(Command $command): User {
        if ($command->getExecutor()) {
            return $command->getExecutor();
        } else {
            $executor = $this->getCurrentUser();
            if (!$executor) {
                throw new InsufficientPrivilegesException('Could not detect the executor: nobody is authenticated');
            }
            EntityUtils::forceSetField($command, $executor, 'executor');
            return $executor;
        }
    }

    public static function bypass(callable $callback) {
        self::$firewallEnabled = false;
        $result = $callback();
        self::$firewallEnabled = true;
        return $result;
    }
}
