<?php
namespace Repeka\Application\Cqrs;

use Repeka\Application\Cqrs\Middleware\FirewallMiddleware;
use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandBus;

trait CommandBusAware {
    /** @var CommandBus */
    private $commandBus;

    /** @required */
    public function setCommandBus(CommandBus $commandBus): void {
        $this->commandBus = $commandBus;
    }

    public function handleCommand(Command $command) {
        return $this->commandBus->handle($command);
    }

    protected function handleCommandBypassingFirewall(Command $command) {
        return FirewallMiddleware::bypass(
            function () use ($command) {
                return $this->handleCommand($command);
            }
        );
    }
}
