<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Exception\InsufficientPrivilegesException;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Resource\ResourceCloneCommand;
use Repeka\Domain\UseCase\Resource\ResourceCloneCommandFirewall;
use Repeka\Tests\Traits\StubsTrait;

class ResourceCloneCommandFirewallTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /* @var User|\PHPUnit_Framework_MockObject_MockObject $user */
    private $executor;
    /** @var ResourceCloneCommandFirewall */
    private $firewall;
    /** @var ResourceKind */
    private $resourceKind;
    private $id;

    protected function setUp() {
        $this->executor = $this->createMock(User::class);
        $this->resourceKind = $this->createResourceKindMock();
        $this->id = 1;
        $parent = $this->createResourceMock($this->id, $this->resourceKind, [SystemMetadata::REPRODUCTOR => [1]]);
        $resourceRepository = $this->createRepositoryStub(ResourceRepository::class, [$parent]);
        $this->firewall = new ResourceCloneCommandFirewall($resourceRepository);
    }

    public function testPassForValidReproductor() {
        $command = new ResourceCloneCommand($this->resourceKind, $this->id, ResourceContents::fromArray([SystemMetadata::PARENT => [1]]));
        $this->executor->expects($this->once())->method('belongsToAnyOfGivenUserGroupsIds')->with([1])->willReturn(true);
        $this->firewall->ensureCanExecute($command, $this->executor);
    }

    public function testFailForInvalidReproductor() {
        $this->expectException(InsufficientPrivilegesException::class);
        $command = new ResourceCloneCommand($this->resourceKind, $this->id, ResourceContents::fromArray([SystemMetadata::PARENT => [1]]));
        $this->executor->expects($this->once())->method('belongsToAnyOfGivenUserGroupsIds')->with([1])->willReturn(false);
        $this->firewall->ensureCanExecute($command, $this->executor);
    }

    public function testPassWhenTopLevel() {
        $command = new ResourceCloneCommand($this->resourceKind, $this->id, ResourceContents::fromArray([SystemMetadata::PARENT => []]));
        $this->firewall->ensureCanExecute($command, $this->executor);
    }
}
