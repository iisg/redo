<?php
namespace Repeka\Tests\Application\Cqrs\Middleware;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Application\Cqrs\Middleware\AuditCommandMiddleware;
use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\AuditedCommand;
use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandAuditor;
use Repeka\Domain\Entity\AuditEntry;
use Repeka\Domain\Exception\DomainException;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AuditCommandMiddlewareTest extends \PHPUnit_Framework_TestCase {
    /** @var AuditCommandMiddleware */
    private $middleware;
    /** @var Command|PHPUnit_Framework_MockObject_MockObject */
    private $auditedCommand;
    /** @var ContainerInterface|PHPUnit_Framework_MockObject_MockObject */
    private $container;
    /** @var CommandAuditor|PHPUnit_Framework_MockObject_MockObject */
    private $auditor;
    /** @var EntityManagerInterface|PHPUnit_Framework_MockObject_MockObject */
    private $entityManager;

    private $wasCalled;

    protected function setUp() {
        $this->container = $this->createMock(ContainerInterface::class);
        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $managerRegistry->method('getManager')->willReturn($this->entityManager);
        $this->middleware = new AuditCommandMiddleware($this->container, $managerRegistry);
        $this->middleware->setTokenStorage($this->createMock(TokenStorageInterface::class));
        $this->auditedCommand = $this->createMock(AuditedCommand::class);
        $this->auditedCommand->method('getCommandName')->willReturn('some_command');
        $this->auditor = $this->createMock(CommandAuditor::class);
    }

    public function testAuditingSuccess() {
        $this->container->expects($this->once())->method('has')->willReturn(true);
        $this->container->expects($this->once())->method('get')->willReturn($this->auditor);
        $this->auditor->expects($this->once())->method('beforeHandling')->with($this->auditedCommand);
        $this->auditor->expects($this->once())->method('afterHandling')
            ->with($this->auditedCommand, 'A')->willReturn(['a']);
        $this->entityManager->expects($this->once())->method('persist')->willReturnCallback(function (AuditEntry $entry) {
            $this->assertTrue($entry->isSuccessful());
            $this->assertEquals(['a'], $entry->getData());
            $this->assertEquals('some_command', $entry->getCommandName());
        });
        $this->middleware->handle($this->auditedCommand, function ($c) {
            $this->assertSame($c, $this->auditedCommand);
            $this->wasCalled = true;
            return 'A';
        });
        $this->assertTrue($this->wasCalled);
    }

    public function testAuditingFailure() {
        $this->expectException(DomainException::class);
        $this->container->expects($this->once())->method('has')->willReturn(true);
        $this->container->expects($this->once())->method('get')->willReturn($this->auditor);
        $this->auditor->expects($this->once())->method('beforeHandling')->with($this->auditedCommand);
        $this->auditor->expects($this->once())->method('afterError')->willReturn(['a']);
        $this->entityManager->expects($this->once())->method('persist')->willReturnCallback(function (AuditEntry $entry) {
            $this->assertFalse($entry->isSuccessful());
            $this->assertEquals(['a'], $entry->getData());
            $this->assertEquals('some_command', $entry->getCommandName());
        });
        $this->middleware->handle($this->auditedCommand, function ($c) {
            $this->assertSame($c, $this->auditedCommand);
            $this->wasCalled = true;
            throw new DomainException("A");
        });
        $this->assertTrue($this->wasCalled);
    }

    public function testDoNotAuditNormalCommand() {
        $this->container->expects($this->never())->method('has')->willReturn(true);
        $this->middleware->handle($this->createMock(AbstractCommand::class), function () {
        });
    }
}
