<?php
namespace Repeka\CoreModule\Bundle\Cqrs\Middleware;

use Repeka\CoreModule\Domain\Cqrs\Command;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class CallCommandHandlerMiddleware implements CommandBusMiddleware {
    private $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function handle(Command $command, callable $next) {
        $handlerName = $this->getHandlerId($command->getCommandName());
        try {
            $handler = $this->container->get($handlerName);
            return $handler->handle($command);
        } catch (ServiceNotFoundException $e) {
            $message = "Could not find handler for the command {$command->getCommandName()} (looking for $handlerName).";
            throw new \InvalidArgumentException($message, 0, $e);
        }
    }

    private function getHandlerId($commandName) {
        return 'command_handler.' . $commandName;
    }
}
