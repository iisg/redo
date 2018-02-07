<?php
namespace Repeka\Tests\Application\Cqrs\Middleware;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Application\Cqrs\Middleware\AdjustCommandMiddleware;
use Repeka\Application\Cqrs\Middleware\ValidateCommandMiddleware;
use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\AdjustableCommand;
use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandAdjuster;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AdjustCommandMiddlewareTest extends \PHPUnit_Framework_TestCase {
    /** @var ValidateCommandMiddleware */
    private $middleware;
    /** @var Command|PHPUnit_Framework_MockObject_MockObject */
    private $adjustableCommand;
    /** @var ContainerInterface|PHPUnit_Framework_MockObject_MockObject */
    private $container;
    /** @var CommandAdjuster|PHPUnit_Framework_MockObject_MockObject */
    private $adjuster;

    private $wasCalled;

    protected function setUp() {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->middleware = new AdjustCommandMiddleware($this->container);
        $this->adjustableCommand = $this->createMock(AdjustableCommand::class);
        $this->adjustableCommand->expects($this->any())->method('getCommandName')->willReturn('some_command');
        $this->adjuster = $this->createMock(CommandAdjuster::class);
    }

    public function testAdjusting() {
        $this->container->expects($this->once())->method('has')->willReturn(true);
        $this->container->expects($this->once())->method('get')->willReturn($this->adjuster);
        $adjustedCommand = $this->createMock(AdjustableCommand::class);
        $this->adjuster->method('adjustCommand')->willReturn($adjustedCommand);
        $this->middleware->handle($this->adjustableCommand, function ($c) use ($adjustedCommand) {
            $this->assertSame($c, $adjustedCommand);
            $this->wasCalled = true;
        });
        $this->assertTrue($this->wasCalled);
    }

    public function testDoNotAdjustNormalCommand() {
        $this->container->expects($this->never())->method('has')->willReturn(true);
        $this->middleware->handle($this->createMock(AbstractCommand::class), function () {
        });
    }
}
