<?php
namespace Repeka\CoreModule\Tests\Bundle\Cqrs\Middleware;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\CoreModule\Bundle\Cqrs\Middleware\CallCommandHandlerMiddleware;
use Repeka\CoreModule\Tests\Domain\Cqrs\SampleCommand;
use Repeka\CoreModule\Tests\Domain\Cqrs\SampleCommandHandler;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class CallCommandHandlerMiddlewareTest extends \PHPUnit_Framework_TestCase {
    /** @var CallCommandHandlerMiddleware */
    private $middleware;
    /** @var PHPUnit_Framework_MockObject_MockObject */
    private $container;

    protected function setUp() {
        $this->container = $this->getMockBuilder(Container::class)
            ->setMethods(['get'])
            ->getMock();
        $this->middleware = new CallCommandHandlerMiddleware($this->container);
    }

    public function testGettingHandlerFromContainer() {
        $this->container->expects($this->once())
            ->method('get')
            ->with($this->equalTo('command_handler.core.sample'))
            ->willReturn(new SampleCommandHandler());
        $this->middleware->handle(new SampleCommand(), function () {
        });
    }

    public function testHandlingCommandByHandler() {
        $command = new SampleCommand();
        $handler = new SampleCommandHandler();
        $this->container->expects($this->once())
            ->method('get')
            ->with($this->equalTo('command_handler.core.sample'))
            ->willReturn($handler);
        $this->middleware->handle($command, function () {
        });
        $this->assertSame($command, $handler->lastCommand);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp('command_handler\.core\.sample')
     */
    public function testFailsWhenContainerDoesNotKnowTheHandler() {
        $this->container->expects($this->once())
            ->method('get')
            ->with($this->equalTo('command_handler.core.sample'))
            ->willThrowException(new ServiceNotFoundException('command_handler.core.sample'));
        $this->middleware->handle(new SampleCommand(), function () {
        });
    }
}
