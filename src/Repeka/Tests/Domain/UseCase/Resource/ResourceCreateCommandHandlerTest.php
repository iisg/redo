<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use Repeka\Domain\Entity\ResourceKind;
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
    private $resourceClass;

    protected function setUp() {
        $this->resourceRepository = $this->createRepositoryStub(ResourceRepository::class);
        $this->fileHelper = $this->createMock(ResourceFileHelper::class);
        $this->handler = new ResourceCreateCommandHandler($this->resourceRepository, $this->fileHelper);
        $this->resourceClass = 'books';
    }

    public function testCreatingResourceWithoutWorkflow() {
        $resourceKind = new ResourceKind([], 'books');
        $command = new ResourceCreateCommand($resourceKind, ['1' => ['AA']], $this->resourceClass);
        $resource = $this->handler->handle($command);
        $this->assertNotNull($resource);
        $this->assertSame($command->getKind(), $resource->getKind());
        $this->assertSame([1 => ['AA']], $resource->getContents());
        $this->assertSame('books', $resource->getResourceClass());
    }

    public function testMissingMetadataRequiredByWorkflowBlockCreation() {
        $this->expectException(DomainException::class);
        $initialPlace = $this->createMock(ResourceWorkflowPlace::class);
        $initialPlace->expects($this->once())->method('resourceHasRequiredMetadata')->willReturn(false);
        $workflow = $this->createMock(ResourceWorkflow::class);
        $workflow->method('getInitialPlace')->willReturn($initialPlace);
        $resourceKind = new ResourceKind([], 'books', $workflow);
        $command = new ResourceCreateCommand($resourceKind, ['1' => ['AA']], $this->resourceClass);
        $this->handler->handle($command);
    }

    public function testCreatingResourceWithWorkflow() {
        $initialPlace = $this->createMock(ResourceWorkflowPlace::class);
        $initialPlace->expects($this->once())->method('resourceHasRequiredMetadata')->willReturn(true);
        $workflow = $this->createMock(ResourceWorkflow::class);
        $workflow->method('getInitialPlace')->willReturn($initialPlace);
        $resourceKind = new ResourceKind([], 'books', $workflow);
        $command = new ResourceCreateCommand($resourceKind, ['1' => ['AA']], $this->resourceClass);
        $resource = $this->handler->handle($command);
        $this->assertNotNull($resource);
        $this->assertEquals($workflow, $resource->getWorkflow());
        $this->assertEquals('books', $resource->getResourceClass());
    }

    public function testMovingFiles() {
        $fileBaseMetadataId = 1;
        $resourceKind = $this->createResourceKindMock([
            $this->createMetadataMock(11, $fileBaseMetadataId, 'file'),
        ]);
        $contents = [$fileBaseMetadataId => []];
        $command = new ResourceCreateCommand($resourceKind, $contents, $this->resourceClass);
        $this->fileHelper->expects($this->once())->method('moveFilesToDestinationPaths');
        $this->handler->handle($command);
    }
}
