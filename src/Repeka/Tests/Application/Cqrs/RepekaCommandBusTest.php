<?php
namespace Repeka\Tests\Application\Cqrs;

use Repeka\Application\Cqrs\RepekaCommandBus;
use Repeka\Tests\Domain\Cqrs\SampleCommand;

class RepekaCommandBusTest extends \PHPUnit_Framework_TestCase {

    public function testCallingMiddleware() {
        $command = new SampleCommand();
        $middleware = new SampleCommandBusMiddleware();
        $bus = new RepekaCommandBus([$middleware]);
        $bus->handle($command);
        $this->assertSame($command, $middleware->latestCommand);
    }

    public function testCallingTwoMiddlewares() {
        $middleware1 = new SampleCommandBusMiddleware();
        $middleware2 = new SampleCommandBusMiddleware();
        $bus = new RepekaCommandBus([$middleware1, $middleware2]);
        $bus->handle(new SampleCommand());
        $this->assertSame($middleware1->latestCommand, $middleware2->latestCommand);
    }
}
