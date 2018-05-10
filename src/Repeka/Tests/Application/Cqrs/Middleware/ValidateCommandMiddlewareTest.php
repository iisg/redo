<?php
namespace Repeka\Tests\Application\Cqrs\Middleware;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Application\Cqrs\Middleware\ValidateCommandMiddleware;
use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandValidator;
use Repeka\Domain\Cqrs\NonValidatedCommand;
use Repeka\Domain\Exception\InvalidCommandException;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ValidateCommandMiddlewareTest extends \PHPUnit_Framework_TestCase {
    /** @var ValidateCommandMiddleware */
    private $middleware;
    /** @var Command|PHPUnit_Framework_MockObject_MockObject */
    private $command;
    /** @var ContainerInterface|PHPUnit_Framework_MockObject_MockObject */
    private $container;
    /** @var CommandValidator|PHPUnit_Framework_MockObject_MockObject */
    private $validator;

    private $wasCalled;

    protected function setUp() {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->middleware = new ValidateCommandMiddleware($this->container);
        $this->command = $this->createMock(AbstractCommand::class);
        $this->command->expects($this->any())->method('getCommandName')->willReturn('some_command');
        $this->validator = $this->createMock(CommandValidator::class);
    }

    public function testPassingValidation() {
        $this->container->expects($this->once())->method('has')->willReturn(true);
        $this->container->expects($this->once())->method('get')->willReturn($this->validator);
        $this->middleware->handle(
            $this->command,
            function ($c) {
                $this->assertSame($c, $this->command);
                $this->wasCalled = true;
            }
        );
        $this->assertTrue($this->wasCalled);
    }

    public function testFailsValidation() {
        $this->expectException('Repeka\Domain\Exception\InvalidCommandException');
        $this->container->expects($this->once())->method('has')->willReturn(true);
        $this->container->expects($this->once())->method('get')->willReturn($this->validator);
        $this->validator->expects($this->once())->method('validate')->with($this->command)
            ->willThrowException(new InvalidCommandException($this->command, [], new \Exception()));
        $this->middleware->handle(
            $this->command,
            function () {
            }
        );
    }

    public function testNoValidatorForCommand() {
        $this->expectException(\InvalidArgumentException::class);
        $this->container->expects($this->once())->method('has')->willReturn(false);
        $this->container->expects($this->never())->method('get');
        $this->middleware->handle(
            $this->command,
            function () {
            }
        );
    }

    public function testNoValidatorForNonValidatedCommand() {
        $command = $this->createMock(NonValidatedCommand::class);
        $this->middleware->handle(
            $command,
            function ($c) use ($command) {
                $this->assertSame($c, $command);
                $this->wasCalled = true;
            }
        );
        $this->assertTrue($this->wasCalled);
    }
}
