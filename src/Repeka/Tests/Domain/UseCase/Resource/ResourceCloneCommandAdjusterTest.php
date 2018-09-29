<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use Repeka\Application\Entity\UserEntity;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Resource\ResourceCloneCommand;
use Repeka\Domain\UseCase\Resource\ResourceCloneCommandAdjuster;
use Repeka\Tests\Traits\StubsTrait;

class ResourceCloneCommandAdjusterTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var  ResourceCloneCommandAdjuster */
    private $adjuster;
    private $id;
    private $kind;
    private $user;
    private $resourceContents;

    protected function setUp() {
        $this->user = new UserEntity();
        $this->id = 1;
        $this->resourceContents = $this->createMock(ResourceContents::class);
        $resource = $this->createResourceMock($this->id);
        $this->kind = $this->createResourceKindMock(1);
        $resourceRepository = $this->createRepositoryStub(ResourceRepository::class, [$resource]);
        $this->adjuster = new ResourceCloneCommandAdjuster($resourceRepository);
    }

    public function testConvertIdToResourceWithExistingId() {
        $command = new ResourceCloneCommand(
            $this->kind,
            $this->id,
            $this->resourceContents,
            $this->user
        );
        $command = $this->adjuster->adjustCommand($command);
        $this->assertInstanceOf(ResourceEntity::class, $command->getResource());
        $this->assertEquals($this->id, $command->getResource()->getId());
    }

    public function testConvertIdToResourceWithNotExistingId() {
        $command = new ResourceCloneCommand(
            $this->kind,
            0,
            $this->resourceContents,
            $this->user
        );
        $command = $this->adjuster->adjustCommand($command);
        $this->assertEquals(null, $command->getResource());
    }
}
