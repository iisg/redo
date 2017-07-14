<?php
namespace Repeka\Tests\Application\Cqrs\Middleware;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Application\Cqrs\Middleware\CallCommandHandlerMiddleware;
use Repeka\Tests\Domain\Cqrs\SampleCommand;
use Repeka\Tests\Domain\Cqrs\SampleCommandHandler;
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
            ->with($this->equalTo(SampleCommand::class . 'Handler'))
            ->willReturn(new SampleCommandHandler());
        $this->middleware->handle(new SampleCommand(), function () {
        });
    }

    public function testHandlingCommandByHandler() {
        $command = new SampleCommand();
        $handler = new SampleCommandHandler();
        $this->container->expects($this->once())
            ->method('get')
            ->with($this->equalTo(SampleCommand::class . 'Handler'))
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
            ->with($this->equalTo(SampleCommand::class . 'Handler'))
            ->willThrowException(new ServiceNotFoundException('command_handler.sample'));
        $this->middleware->handle(new SampleCommand(), function () {
        });
    }
}
