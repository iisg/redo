<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Exception\InsufficientPrivilegesException;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Resource\ResourceMultipleCloneCommand;
use Repeka\Domain\UseCase\Resource\ResourceMultipleCloneCommandFirewall;
use Repeka\Tests\Traits\StubsTrait;

class ResourceMultipleCloneCommandFirewallTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /* @var User|\PHPUnit_Framework_MockObject_MockObject $user */
    private $executor;
    /** @var ResourceMultipleCloneCommandFirewall */
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
        $this->firewall = new ResourceMultipleCloneCommandFirewall($resourceRepository);
    }

    public function testPassForValidReproductor() {
        $command = new ResourceMultipleCloneCommand(
            $this->resourceKind,
            $this->id,
            ResourceContents::fromArray([SystemMetadata::PARENT => [1]])
        );
        $this->executor->expects($this->once())->method('belongsToAnyOfGivenUserGroupsIds')->with([1])->willReturn(true);
        $this->firewall->ensureCanExecute($command, $this->executor);
    }

    public function testFailForInvalidReproductor() {
        $this->expectException(InsufficientPrivilegesException::class);
        $command = new ResourceMultipleCloneCommand(
            $this->resourceKind,
            $this->id,
            ResourceContents::fromArray([SystemMetadata::PARENT => [1]])
        );
        $this->executor->expects($this->once())->method('belongsToAnyOfGivenUserGroupsIds')->with([1])->willReturn(false);
        $this->firewall->ensureCanExecute($command, $this->executor);
    }

    public function testPassWhenTopLevel() {
        $command = new ResourceMultipleCloneCommand(
            $this->resourceKind,
            $this->id,
            ResourceContents::fromArray([SystemMetadata::PARENT => []])
        );
        $this->firewall->ensureCanExecute($command, $this->executor);
    }
}
