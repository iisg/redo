<?php
namespace Repeka\Tests\Application\Cqrs\Middleware;

use Repeka\Application\Cqrs\Event\BeforeCommandHandlingEvent;
use Repeka\Application\Cqrs\Event\CommandErrorEvent;
use Repeka\Application\Cqrs\Event\CommandHandledEvent;
use Repeka\Application\Cqrs\Event\CqrsCommandEvent;
use Repeka\Application\Cqrs\Middleware\DispatchCommandEventsMiddleware;
use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\UseCase\Resource\ResourceQuery;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @SuppressWarnings("PHPMD.UnusedLocalVariable")
 */
class DispatchCommandEventsMiddlewareTest extends \PHPUnit_Framework_TestCase {
    /** @var DispatchCommandEventsMiddleware */
    private $middleware;
    /** @var EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $dispatcher;

    protected function setUp() {
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->middleware = new DispatchCommandEventsMiddleware($this->dispatcher);
    }

    public function testDispatchingSuccess() {
        $this->dispatcher->method('dispatch')->withConsecutive(
            ['command_before.resource', $this->isInstanceOf(BeforeCommandHandlingEvent::class)],
            ['command_handled.resource', $this->isInstanceOf(CommandHandledEvent::class)]
        );
        $this->middleware->handle(
            new ResourceQuery(1),
            function () {
            }
        );
    }

    public function testDispatchingError() {
        $this->expectException(\RuntimeException::class);
        $this->dispatcher->method('dispatch')->withConsecutive(
            ['command_before.resource', $this->isInstanceOf(BeforeCommandHandlingEvent::class)],
            ['command_error.resource', $this->isInstanceOf(CommandErrorEvent::class)]
        );
        $this->middleware->handle(
            new ResourceQuery(1),
            function () {
                throw new \RuntimeException();
            }
        );
    }

    public function testPassingReturnValue() {
        $this->dispatcher->method('dispatch')->willReturnCallback(
            function (string $name, CqrsCommandEvent $event) {
                if ($event instanceof CommandHandledEvent) {
                    $this->assertEquals(2, $event->getResult());
                }
            }
        );
        $this->middleware->handle(
            new ResourceQuery(1),
            function () {
                return 2;
            }
        );
    }

    public function testReplacingCommand() {
        $this->dispatcher->method('dispatch')->willReturnCallback(
            function (string $name, CqrsCommandEvent $event) {
                if ($event instanceof BeforeCommandHandlingEvent) {
                    $event->replaceCommand(new ResourceQuery(2));
                }
            }
        );
        $this->middleware->handle(
            new ResourceQuery(1),
            function (Command $command) {
                $this->assertEquals(2, $command->getId());
            }
        );
    }
}
