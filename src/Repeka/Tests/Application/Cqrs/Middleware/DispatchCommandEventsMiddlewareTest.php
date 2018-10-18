<?php
namespace Repeka\Tests\Application\Cqrs\Middleware;

use Repeka\Application\Cqrs\Middleware\DispatchCommandEventsMiddleware;
use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\UseCase\Metadata\MetadataGetQuery;
use Repeka\Domain\UseCase\Resource\ResourceQuery;

/**
 * @SuppressWarnings("PHPMD.UnusedLocalVariable")
 */
class DispatchCommandEventsMiddlewareTest extends \PHPUnit_Framework_TestCase {
    /** @var DispatchCommandEventsMiddleware */
    private $middleware;
    /** @var SampleCommandEventsListener */
    private $listener;

    protected function setUp() {
        $this->listener = new SampleCommandEventsListener();
        $this->middleware = new DispatchCommandEventsMiddleware([$this->listener]);
    }

    public function testDispatchingSuccess() {
        $this->middleware->handle(
            new ResourceQuery(1),
            function () {
            }
        );
        $this->assertCount(1, $this->listener->before);
        $this->assertCount(1, $this->listener->handled);
        $this->assertCount(0, $this->listener->error);
    }

    public function testNotHandlingUnsubscribedEvent() {
        $this->middleware->handle(
            new MetadataGetQuery(1),
            function () {
            }
        );
        $this->assertEmpty($this->listener->before);
        $this->assertEmpty($this->listener->handled);
        $this->assertEmpty($this->listener->error);
    }

    public function testDispatchingError() {
        try {
            $this->middleware->handle(
                new ResourceQuery(1),
                function () {
                    throw new \RuntimeException();
                }
            );
            $this->fail('Exception expected.');
        } catch (\RuntimeException $exception) {
            $this->assertCount(1, $this->listener->before);
            $this->assertCount(0, $this->listener->handled);
            $this->assertCount(1, $this->listener->error);
        }
    }

    public function testPassingReturnValue() {
        $this->middleware->handle(
            new ResourceQuery(1),
            function () {
                return 2;
            }
        );
        $this->assertEquals(2, $this->listener->handled[0]->getResult());
    }

    public function testReplacingCommand() {
        $this->listener->commandToReplace = new ResourceQuery(2);
        $this->middleware->handle(
            new ResourceQuery(1),
            function (Command $command) {
                $this->assertEquals(2, $command->getId());
            }
        );
    }

    public function testPassingDataFromBeforeToAfterEvent() {
        $this->listener->dataToSet = 'unicorn';
        $this->middleware->handle(
            new ResourceQuery(1),
            function () {
                return 2;
            }
        );
        $this->assertEquals('unicorn', $this->listener->handled[0]->getDataFromBeforeEvent(SampleCommandEventsListener::class));
    }
}
