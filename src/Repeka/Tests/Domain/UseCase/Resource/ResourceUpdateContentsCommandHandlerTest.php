<?php
namespace Repeka\Tests\UseCase\Resource;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\ResourceWorkflowPlace;
use Repeka\Domain\Exception\DomainException;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Upload\ResourceFileHelper;
use Repeka\Domain\UseCase\Resource\ResourceUpdateContentsCommand;
use Repeka\Domain\UseCase\Resource\ResourceUpdateContentsCommandHandler;
use Repeka\Tests\Traits\StubsTrait;

class ResourceUpdateContentsCommandHandlerTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceEntity|PHPUnit_Framework_MockObject_MockObject */
    private $resource;

    /** @var ResourceUpdateContentsCommand */
    private $command;

    /** @var ResourceRepository|PHPUnit_Framework_MockObject_MockObject */
    private $resourceRepository;
    /** @var ResourceFileHelper|PHPUnit_Framework_MockObject_MockObject */
    private $fileHelper;

    /** @var ResourceUpdateContentsCommandHandler */
    private $handler;

    protected function setUp() {
        $this->resource = $this->createMock(ResourceEntity::class);
        $this->command = new ResourceUpdateContentsCommand($this->resource, ['1' => ['AA']]);
        $this->resourceRepository = $this->createRepositoryStub(ResourceRepository::class);
        $this->fileHelper = $this->createMock(ResourceFileHelper::class);
        $this->handler = new ResourceUpdateContentsCommandHandler($this->resourceRepository, $this->fileHelper);
    }

    public function testUpdatingResource() {
        $this->resource->expects($this->once())->method('updateContents')->with(['1' => ['AA']]);
        $resource = $this->handler->handle($this->command);
        $this->assertSame($this->resource, $resource);
    }

    public function testMissingMetadataRequiredByWorkflowBlockUpdate() {
        $this->expectException(DomainException::class);
        $place = $this->createMock(ResourceWorkflowPlace::class);
        $place->expects($this->once())->method('resourceHasRequiredMetadata')->willReturn(false);
        $workflow = $this->createMock(ResourceWorkflow::class);
        $workflow->method('getPlaces')->willReturn([$place]);
        $this->resource->method('getWorkflow')->willReturn($workflow);
        $this->handler->handle($this->command);
    }

    public function testUpdatingResourceWithWorkflow() {
        $place = $this->createMock(ResourceWorkflowPlace::class);
        $place->expects($this->once())->method('resourceHasRequiredMetadata')->willReturn(true);
        $workflow = $this->createMock(ResourceWorkflow::class);
        $workflow->method('getPlaces')->willReturn([$place]);
        $this->resource->method('getWorkflow')->willReturn($workflow);
        $resource = $this->handler->handle($this->command);
        $this->assertEquals($this->resource, $resource);
    }

    public function testMovingFiles() {
        $resource = $this->createMock(ResourceEntity::class);
        $command = new ResourceUpdateContentsCommand($resource, []);
        $this->fileHelper->expects($this->once())->method('moveFilesToDestinationPaths');
        $this->handler->handle($command);
    }
}
