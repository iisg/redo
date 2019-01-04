<?php
namespace Repeka\Tests\Application\Cqrs\Middleware;

use Repeka\Application\Cqrs\Middleware\FirewallMiddleware;
use Repeka\Application\Entity\UserEntity;
use Repeka\Domain\Constants\SystemRole;
use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\ResourceClassAwareCommand;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Exception\InsufficientPrivilegesException;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

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

    public function testFirewallSetsExecutor() {
        $user = $this->createMock(UserEntity::class);
        $tokenStorage = $this->createMock(TokenStorage::class);
        $token = $this->createMock(TokenInterface::class);
        $tokenStorage->method('getToken')->willReturn($token);
        $token->method('getUser')->willReturn($user);
        $this->middleware->setTokenStorage($tokenStorage);
        $command = ResourceListQuery::builder()->build();
        $this->middleware->handle(
            $command,
            function (Command $firewalledCommand) use ($user) {
                $this->assertEquals($user, $firewalledCommand->getExecutor());
            }
        );
    }

    public function testFirewallDoesNotSetExecutorIfDisabled() {
        $user = $this->createMock(UserEntity::class);
        $tokenStorage = $this->createMock(TokenStorage::class);
        $token = $this->createMock(TokenInterface::class);
        $tokenStorage->method('getToken')->willReturn($token);
        $token->method('getUser')->willReturn($user);
        $this->middleware->setTokenStorage($tokenStorage);
        $command = ResourceListQuery::builder()->build();
        $this->assertNull($command->getExecutor());
        FirewallMiddleware::bypass(
            function () use ($command) {
                $this->middleware->handle(
                    $command,
                    function (Command $firewalledCommand) {
                        $this->assertNull($firewalledCommand->getExecutor());
                    }
                );
            }
        );
    }

    private function createCommand(array $executorRoles = [], ?SystemRole $requiredRole = null, ?string $resourceClass = null): Command {
        $command = $this->createMock($resourceClass ? ResourceClassAwareCommand::class : Command::class);
        $command->method('getRequiredRole')->willReturn($requiredRole);
        if ($resourceClass) {
            $command->method('getResourceClass')->willReturn($resourceClass);
        }
        $executor = $this->createMock(User::class);
        $executor->method('hasRole')->willReturnCallback(
            function ($role) use ($executorRoles) {
                return in_array($role, $executorRoles);
            }
        );
        $command->method('getExecutor')->willReturn($executor);
        return $command;
    }
}
