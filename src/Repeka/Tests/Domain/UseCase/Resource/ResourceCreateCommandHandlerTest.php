<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
use Repeka\Domain\Exception\DomainException;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Upload\ResourceFileHelper;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommand;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommandHandler;
use Repeka\Tests\Traits\StubsTrait;

class ResourceCreateCommandHandlerTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $resourceRepository;
    /** @var ResourceFileHelper|\PHPUnit_Framework_MockObject_MockObject */
    private $fileHelper;

    /** @var ResourceCreateCommandHandler */
    private $handler;

    protected function setUp() {
        $this->resourceRepository = $this->createRepositoryStub(ResourceRepository::class);
        $this->fileHelper = $this->createMock(ResourceFileHelper::class);
        $this->handler = new ResourceCreateCommandHandler($this->resourceRepository, $this->fileHelper);
    }

    public function testCreatingResourceWithoutWorkflow() {
        $resourceKind = $this->createResourceKindMock();
        $contents = ResourceContents::fromArray(['1' => ['AA']]);
        $command = new ResourceCreateCommand($resourceKind, $contents);
        $resource = $this->handler->handle($command);
        $this->assertNotNull($resource);
        $this->assertSame($command->getKind(), $resource->getKind());
        $this->assertEquals($contents, $resource->getContents());
        $this->assertSame('books', $resource->getResourceClass());
    }

    public function testMissingMetadataRequiredByWorkflowBlockCreation() {
        $this->expectException(DomainException::class);
        $initialPlace = $this->createMock(ResourceWorkflowPlace::class);
        $initialPlace->expects($this->once())->method('resourceHasRequiredMetadata')->willReturn(false);
        $workflow = $this->createMock(ResourceWorkflow::class);
        $workflow->method('getInitialPlace')->willReturn($initialPlace);
        $resourceKind = $this->createResourceKindMock(1, 'books', [], $workflow);
        $command = new ResourceCreateCommand($resourceKind, ResourceContents::fromArray(['1' => ['AA']]));
        $this->handler->handle($command);
    }

    public function testCreatingResourceWithWorkflow() {
        $initialPlace = $this->createWorkflowPlaceMock('key');
        $workflow = $this->createMock(ResourceWorkflow::class);
        $workflow->method('getInitialPlace')->willReturn($initialPlace);
        $resourceKind = $this->createResourceKindMock(1, 'books', [], $workflow);
        $command = new ResourceCreateCommand($resourceKind, ResourceContents::fromArray(['1' => ['AA']]));
        $resource = $this->handler->handle($command);
        $this->assertNotNull($resource);
        $this->assertEquals($workflow, $resource->getWorkflow());
        $this->assertEquals('books', $resource->getResourceClass());
    }

    public function testCreatedResourceEntersTheFirstPlaceOfWorkflow() {
        $initialPlace = $this->createWorkflowPlaceMock('key');
        $workflow = $this->createMock(ResourceWorkflow::class);
        $workflow->method('getInitialPlace')->willReturn($initialPlace);
        $workflow->expects($this->once())->method('setCurrentPlaces')->with($this->isInstanceOf(ResourceEntity::class), ['key']);
        $resourceKind = $this->createResourceKindMock(1, 'books', [], $workflow);
        $command = new ResourceCreateCommand($resourceKind, ResourceContents::empty());
        $this->handler->handle($command);
    }

    public function testMovingFiles() {
        $fileBaseMetadataId = 1;
        $resourceKind = $this->createResourceKindMock(1, 'books', [
            $this->createMetadataMock(11, $fileBaseMetadataId, MetadataControl::FILE()),
        ]);
        $contents = ResourceContents::fromArray([$fileBaseMetadataId => []]);
        $command = new ResourceCreateCommand($resourceKind, $contents);
        $this->fileHelper->expects($this->once())->method('moveFilesToDestinationPaths');
        $this->handler->handle($command);
    }
}
