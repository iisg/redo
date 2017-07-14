<?php

namespace Repeka\Application\Cqrs\Middleware;

use Repeka\Domain\Cqrs\Command;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class CallCommandHandlerMiddleware implements CommandBusMiddleware {
    private $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function handle(Command $command, callable $next) {
        $handlerName = get_class($command) . 'Handler';
        try {
            $handler = $this->container->get($handlerName);
            return $handler->handle($command);
        } catch (ServiceNotFoundException $e) {
            $message = "Could not find handler for the command {$command->getCommandName()} (looking for $handlerName).";
            throw new \InvalidArgumentException($message, 0, $e);
        }
    }
}
