<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\ResourceWorkflowPlace;
use Repeka\Domain\Exception\DomainException;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommand;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommandHandler;
use Repeka\Domain\Workflow\ResourceWorkflowTransitionHelper;
use Repeka\Tests\Traits\StubsTrait;

class ResourceCreateCommandHandlerTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    private $resourceRepository;

    /** @var ResourceCreateCommandHandler */
    private $handler;

    protected function setUp() {
        $this->resourceRepository = $this->createRepositoryStub(ResourceRepository::class);
        $this->handler = new ResourceCreateCommandHandler($this->resourceRepository);
    }

    public function testCreatingResourceWithoutWorkflow() {
        $resourceKind = new ResourceKind([]);
        $command = new ResourceCreateCommand($resourceKind, ['1' => ['AA']]);
        $resource = $this->handler->handle($command);
        $this->assertNotNull($resource);
        $this->assertSame($command->getKind(), $resource->getKind());
        $this->assertSame([1 => ['AA']], $resource->getContents());
    }

    public function testMissingMetadataRequiredByWorkflowBlockCreation() {
        $this->expectException(DomainException::class);
        $helper = $this->createMock(ResourceWorkflowTransitionHelper::class);
        $helper->expects($this->once())->method('placeIsPermittedByResourceMetadata')->willReturn(false);
        $initialPlace = $this->createMock(ResourceWorkflowPlace::class);
        $workflow = $this->createMock(ResourceWorkflow::class);
        $workflow->method('getTransitionHelper')->willReturn($helper);
        $workflow->method('getPlaces')->willReturn([$initialPlace]);
        $resourceKind = new ResourceKind([], $workflow);
        $command = new ResourceCreateCommand($resourceKind, ['1' => ['AA']]);
        $this->handler->handle($command);
    }

    public function testCreatingResourceWithWorkflow() {
        $helper = $this->createMock(ResourceWorkflowTransitionHelper::class);
        $helper->expects($this->once())->method('placeIsPermittedByResourceMetadata')->willReturn(true);
        $initialPlace = $this->createMock(ResourceWorkflowPlace::class);
        $workflow = $this->createMock(ResourceWorkflow::class);
        $workflow->method('getTransitionHelper')->willReturn($helper);
        $workflow->method('getPlaces')->willReturn([$initialPlace]);
        $resourceKind = new ResourceKind([], $workflow);
        $command = new ResourceCreateCommand($resourceKind, ['1' => ['AA']]);
        $resource = $this->handler->handle($command);
        $this->assertNotNull($resource);
        $this->assertEquals($workflow, $resource->getWorkflow());
    }
}
