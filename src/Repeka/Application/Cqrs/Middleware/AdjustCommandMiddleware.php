<?php
namespace Repeka\Application\Cqrs\Middleware;

use Repeka\Domain\Cqrs\AdjustableCommand;
use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandAdjuster;
use Repeka\Domain\Utils\EntityUtils;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AdjustCommandMiddleware implements CommandBusMiddleware {
    private $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function handle(Command $command, callable $next) {
        if ($command instanceof AdjustableCommand) {
            $adjusterId = $this->getAdjusterId($command);
            if ($this->container->has($adjusterId)) {
                /** @var CommandAdjuster $adjuster */
                $adjuster = $this->container->get($adjusterId);
                $executor = $command->getExecutor();
                $command = $adjuster->adjustCommand($command);
                EntityUtils::forceSetField($command, $executor, 'executor');
            } else {
                throw new \InvalidArgumentException(
                    "Could not find an adjuster for the {$command->getCommandName()}. "
                    . "Looking for the {$adjusterId}. "
                    . "If the command is not meant to have an adjuster, remove the AdjustableCommand interface from it."
                );
            }
        }
        return $next($command);
    }

    private function getAdjusterId(Command $command) {
        return $command->getCommandClassName() . 'Adjuster';
    }
}
