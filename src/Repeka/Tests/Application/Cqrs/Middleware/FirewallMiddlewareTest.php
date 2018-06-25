<?php
namespace Repeka\Tests\Application\Cqrs\Middleware;

use Repeka\Application\Cqrs\Middleware\FirewallMiddleware;
use Repeka\Domain\Constants\SystemRole;
use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\ResourceClassAwareCommand;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Exception\InsufficientPrivilegesException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @SuppressWarnings("PHPMD.UnusedLocalVariable")
 */
class FirewallMiddlewareTest extends \PHPUnit_Framework_TestCase {
    /** @var FirewallMiddleware */
    private $middleware;

    private $noopCallback;
    /** @var ContainerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $container;

    protected function setUp() {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->middleware = new FirewallMiddleware($this->container);
        $this->noopCallback = function () {
        };
    }

    public function testDoesNothingIfNoRoleIsRequired() {
        $command = $this->createCommand();
        $this->middleware->handle(
            $command,
            function (Command $allowedCommand) use ($command) {
                $this->assertSame($allowedCommand, $command);
            }
        );
    }

    public function testThrowsWhenRequiredRoleIsMissing() {
        $this->expectException(InsufficientPrivilegesException::class);
        $command = $this->createCommand([], SystemRole::ADMIN());
        $this->middleware->handle($command, $this->noopCallback);
    }

    public function testAllowsWhenHasRequiredRole() {
        $command = $this->createCommand([SystemRole::ADMIN()->roleName()], SystemRole::ADMIN());
        $this->middleware->handle($command, $this->noopCallback);
    }

    public function testThrowsWhenRequiredRoleForResourceClassMissing() {
        $this->expectException(InsufficientPrivilegesException::class);
        $command = $this->createCommand([SystemRole::ADMIN()->roleName('horses')], SystemRole::ADMIN(), 'unicorns');
        $this->middleware->handle($command, $this->noopCallback);
    }

    public function testAllowsWhenHasRequiredRoleForResourceClass() {
        $command = $this->createCommand([SystemRole::ADMIN()->roleName('unicorns')], SystemRole::ADMIN(), 'unicorns');
        $this->middleware->handle($command, $this->noopCallback);
    }

    public function testAllowsWhenFirewallIsDisabled() {
        FirewallMiddleware::bypass(
            function () {
                $command = $this->createCommand([], SystemRole::ADMIN());
                $this->middleware->handle($command, $this->noopCallback);
            }
        );
    }

    public function testFirewallIsEnabledAfterAMoment() {
        $this->testAllowsWhenFirewallIsDisabled();
        $this->testThrowsWhenRequiredRoleIsMissing();
    }

    private function createCommand(array $executorRoles = [], ?SystemRole $requiredRole = null, ?string $resourceClass = null): Command {
        $command = $this->createMock($resourceClass ? ResourceClassAwareCommand::class : Command::class);
        $command->method('getRequiredRole')->willReturn($requiredRole);
        if ($resourceClass) {
            $command->method('getResourceClass')->willReturn($resourceClass);
        }
        $executor = $this->createMock(User::class);
        $executor->method('getRoles')->willReturn($executorRoles);
        $command->method('getExecutor')->willReturn($executor);
        return $command;
    }
}
