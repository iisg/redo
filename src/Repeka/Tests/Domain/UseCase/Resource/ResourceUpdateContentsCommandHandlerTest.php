<?php
namespace Repeka\Tests\UseCase\Resource;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\ResourceWorkflowPlace;
use Repeka\Domain\Exception\DomainException;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Resource\ResourceUpdateContentsCommand;
use Repeka\Domain\UseCase\Resource\ResourceUpdateContentsCommandHandler;
use Repeka\Domain\Workflow\ResourceWorkflowTransitionHelper;
use Repeka\Tests\Traits\StubsTrait;

class ResourceUpdateContentsCommandHandlerTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceEntity|PHPUnit_Framework_MockObject_MockObject */
    private $resource;

    /** @var ResourceUpdateContentsCommand */
    private $command;

    private $resourceRepository;

    /** @var ResourceUpdateContentsCommandHandler */
    private $handler;

    protected function setUp() {
        $this->resource = $this->createMock(ResourceEntity::class);
        $this->command = new ResourceUpdateContentsCommand($this->resource, ['1' => ['AA']]);
        $this->resourceRepository = $this->createRepositoryStub(ResourceRepository::class);
        $this->handler = new ResourceUpdateContentsCommandHandler($this->resourceRepository);
    }

    public function testUpdatingResource() {
        $this->resource->expects($this->once())->method('updateContents')->with(['1' => ['AA']]);
        $resource = $this->handler->handle($this->command);
        $this->assertSame($this->resource, $resource);
    }

    public function testMissingMetadataRequiredByWorkflowBlockUpdate() {
        $this->expectException(DomainException::class);
        $helper = $this->createMock(ResourceWorkflowTransitionHelper::class);
        $helper->expects($this->once())->method('placeIsPermittedByResourceMetadata')->willReturn(false);
        $place = $this->createMock(ResourceWorkflowPlace::class);
        $workflow = $this->createMock(ResourceWorkflow::class);
        $workflow->method('getTransitionHelper')->willReturn($helper);
        $workflow->method('getPlaces')->willReturn([$place]);
        $this->resource->method('getWorkflow')->willReturn($workflow);
        $this->handler->handle($this->command);
    }

    public function testUpdatingResourceWithWorkflow() {
        $helper = $this->createMock(ResourceWorkflowTransitionHelper::class);
        $helper->expects($this->once())->method('placeIsPermittedByResourceMetadata')->willReturn(true);
        $place = $this->createMock(ResourceWorkflowPlace::class);
        $workflow = $this->createMock(ResourceWorkflow::class);
        $workflow->method('getTransitionHelper')->willReturn($helper);
        $workflow->method('getPlaces')->willReturn([$place]);
        $this->resource->method('getWorkflow')->willReturn($workflow);
        $resource = $this->handler->handle($this->command);
        $this->assertEquals($this->resource, $resource);
    }
}
