<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Exception\InsufficientPrivilegesException;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommand;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommandFirewall;
use Repeka\Tests\Traits\StubsTrait;

class ResourceCreateCommandFirewallTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /* @var User|\PHPUnit_Framework_MockObject_MockObject $user */
    private $executor;
    /** @var ResourceCreateCommandFirewall */
    private $firewall;
    /** @var ResourceKind */
    private $resourceKind;

    protected function setUp() {
        $this->executor = $this->createMock(User::class);
        $this->resourceKind = $this->createResourceKindMock();
        $parent = $this->createResourceMock(1, $this->resourceKind, [SystemMetadata::REPRODUCTOR => [1]]);
        $resourceRepository = $this->createRepositoryStub(ResourceRepository::class, [$parent]);
        $this->firewall = new ResourceCreateCommandFirewall($resourceRepository);
    }

    public function testPassForValidReproductor() {
        $command = new ResourceCreateCommand($this->resourceKind, ResourceContents::fromArray([SystemMetadata::PARENT => [1]]));
        $this->executor->expects($this->once())->method('belongsToAnyOfGivenUserGroupsIds')->with([1])->willReturn(true);
        $this->firewall->ensureCanExecute($command, $this->executor);
    }

    public function testFailForInvalidReproductor() {
        $this->expectException(InsufficientPrivilegesException::class);
        $command = new ResourceCreateCommand($this->resourceKind, ResourceContents::fromArray([SystemMetadata::PARENT => [1]]));
        $this->executor->expects($this->once())->method('belongsToAnyOfGivenUserGroupsIds')->with([1])->willReturn(false);
        $this->firewall->ensureCanExecute($command, $this->executor);
    }

    public function testPassWhenTopLevel() {
        $command = new ResourceCreateCommand($this->resourceKind, ResourceContents::fromArray([SystemMetadata::PARENT => []]));
        $this->firewall->ensureCanExecute($command, $this->executor);
    }
}
