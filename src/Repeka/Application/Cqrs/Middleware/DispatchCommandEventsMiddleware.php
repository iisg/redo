<?php
namespace Repeka\Application\Cqrs\Middleware;

use Repeka\Application\Cqrs\Event\BeforeCommandHandlingEvent;
use Repeka\Application\Cqrs\Event\CommandErrorEvent;
use Repeka\Application\Cqrs\Event\CommandHandledEvent;
use Repeka\Application\Cqrs\Event\CqrsCommandEvent;
use Repeka\Domain\Cqrs\Command;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DispatchCommandEventsMiddleware implements CommandBusMiddleware {
    /** @var EventDispatcherInterface */
    private $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher) {
        $this->dispatcher = $dispatcher;
    }

    public function handle(Command $command, callable $next) {
        $beforeCommandHandlingEvent = new BeforeCommandHandlingEvent($command);
        $this->dispatch($beforeCommandHandlingEvent);
        $command = $beforeCommandHandlingEvent->getCommand();
        try {
            $result = $next($command);
            $this->dispatch(new CommandHandledEvent($command, $result));
            return $result;
        } catch (\Exception $e) {
            $this->dispatch(new CommandErrorEvent($command, $e));
            throw $e;
        }
    }

    private function dispatch(CqrsCommandEvent $event) {
        $this->dispatcher->dispatch($event->getEventName(), $event);
    }
}
