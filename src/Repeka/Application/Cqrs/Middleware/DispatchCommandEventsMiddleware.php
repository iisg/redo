<?php
namespace Repeka\Application\Cqrs\Middleware;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\Event\BeforeCommandHandlingEvent;
use Repeka\Domain\Cqrs\Event\CommandErrorEvent;
use Repeka\Domain\Cqrs\Event\CommandEventsListener;
use Repeka\Domain\Cqrs\Event\CommandHandledEvent;
use Repeka\Domain\Cqrs\Event\CqrsCommandEvent;

class DispatchCommandEventsMiddleware implements CommandBusMiddleware {
    /** @var iterable|CommandEventsListener[] */
    private $commandEventsListeners;

    public function __construct(iterable $commandEventsListeners) {
        $this->commandEventsListeners = $commandEventsListeners;
    }

    public function handle(Command $command, callable $next) {
        $beforeCommandHandlingEvent = new BeforeCommandHandlingEvent($command);
        $this->dispatch($beforeCommandHandlingEvent);
        $command = $beforeCommandHandlingEvent->getCommand();
        $dataForHandledEvent = $beforeCommandHandlingEvent->getDataForHandledEvent();
        try {
            $result = $next($command);
            $this->dispatch(new CommandHandledEvent($command, $result, $dataForHandledEvent));
            return $result;
        } catch (\Exception $e) {
            $this->dispatch(new CommandErrorEvent($command, $e));
            throw $e;
        }
    }

    private function dispatch(CqrsCommandEvent $event) {
        foreach ($this->commandEventsListeners as $listener) {
            $listener->handleCommandEvent($event);
        }
    }
}
