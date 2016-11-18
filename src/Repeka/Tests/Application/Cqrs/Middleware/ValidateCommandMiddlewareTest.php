<?php
namespace Repeka\Tests\Application\Cqrs\Middleware;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Application\Cqrs\Middleware\ValidateCommandMiddleware;
use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandValidator;
use Repeka\Domain\Exception\InvalidCommandException;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ValidateCommandMiddlewareTest extends \PHPUnit_Framework_TestCase {
    /** @var ValidateCommandMiddleware */
    private $middleware;
    /** @var PHPUnit_Framework_MockObject_MockObject */
    private $command;
    /** @var PHPUnit_Framework_MockObject_MockObject */
    private $container;
    /** @var PHPUnit_Framework_MockObject_MockObject */
    private $validator;

    protected function setUp() {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->middleware = new ValidateCommandMiddleware($this->container);
        $this->command = $this->createMock(Command::class);
        $this->command->expects($this->any())->method('getCommandName')->willReturn('some_command');
        $this->validator = $this->createMock(CommandValidator::class);
    }

    public function testPassingValidation() {
        $this->container->expects($this->once())->method('has')->with('command_validator.some_command')->willReturn(true);
        $this->container->expects($this->once())->method('get')->with('command_validator.some_command')->willReturn($this->validator);
        $this->middleware->handle($this->command, function ($c) {
            $this->assertSame($c, $this->command);
            $this->wasCalled = true;
        });
        $this->assertTrue($this->wasCalled);
    }

    /**
     * @expectedException Repeka\Domain\Exception\InvalidCommandException
     */
    public function testFailsValidation() {
        $this->container->expects($this->once())->method('has')->with('command_validator.some_command')->willReturn(true);
        $this->container->expects($this->once())->method('get')->with('command_validator.some_command')->willReturn($this->validator);
        $this->validator->expects($this->once())->method('validate')->with($this->command)
            ->willThrowException(new InvalidCommandException([], new \Exception()));
        $this->middleware->handle($this->command, function () {
        });
    }

    public function testNoValidatorForCommand() {
        $this->container->expects($this->once())->method('has')->with('command_validator.some_command')->willReturn(false);
        $this->container->expects($this->never())->method('get');
        $this->middleware->handle($this->command, function ($c) {
            $this->assertSame($c, $this->command);
            $this->wasCalled = true;
        });
        $this->assertTrue($this->wasCalled);
    }
}
