<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use Repeka\Application\Entity\UserEntity;
use Repeka\Domain\Constants\SystemTransition;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowTransition;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Upload\ResourceFileHelper;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommandHandler;
use Repeka\Tests\Traits\StubsTrait;

/** @SuppressWarnings(PHPMD.CouplingBetweenObjects) */
class ResourceTransitionCommandHandlerTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceEntity|\PHPUnit_Framework_MockObject_MockObject */
    private $resource;
    /** @var User */
    private $executor;
    /** @var ResourceWorkflow|\PHPUnit_Framework_MockObject_MockObject */
    private $workflow;

    /** @var ResourceTransitionCommandHandler */
    private $handler;
    private $fileHelper;
    private $user;

    protected function setUp() {
        $this->user = new UserEntity();
        $this->workflow = $this->createMock(ResourceWorkflow::class);
        $this->resource = $this->createMock(ResourceEntity::class);
        $this->resource->method('hasWorkflow')->willReturn(true);
        $resourceRepository = $this->createRepositoryStub(ResourceRepository::class);
        $this->executor = $this->createMock(User::class);
        $this->fileHelper = $this->createMock(ResourceFileHelper::class);
        $this->handler = new ResourceTransitionCommandHandler($resourceRepository, $this->fileHelper);
    }

    public function testTransition() {
        $transition = $this->createWorkflowTransitionMock([], [], [], 't1');
        $command = new ResourceTransitionCommand($this->resource, ResourceContents::empty(), $transition, $this->executor);
        $this->resource->expects($this->once())->method('applyTransition')->with('t1');
        $this->handler->handle($command);
    }

    public function testUpdatingResourceWithoutWorkflow() {
        $transition = $this->createMock(ResourceWorkflowTransition::class);
        $command = new ResourceTransitionCommand($this->resource, ResourceContents::fromArray(['1' => ['AA']]), $transition, $this->user);
        $this->resource->expects($this->once())->method('updateContents')->with(ResourceContents::fromArray(['1' => ['AA']]));
        $resource = $this->handler->handle($command);
        $this->assertSame($this->resource, $resource);
    }

    public function testUpdatingResourceWithWorkflow() {
        $transition = $this->createWorkflowTransitionMock([], [], [], 't1');
        $command = new ResourceTransitionCommand($this->resource, ResourceContents::fromArray(['1' => ['AA']]), $transition, $this->user);
        $this->resource->expects($this->once())->method('updateContents')->with(ResourceContents::fromArray(['1' => ['AA']]));
        $this->resource->expects($this->once())->method('hasWorkflow')->willReturn(true);
        $this->resource->expects($this->once())->method('applyTransition')->with('t1');
        $resource = $this->handler->handle($command);
        $this->assertEquals($this->resource, $resource);
    }

    public function testMovingFiles() {
        $resource = $this->createResourceMock(1);
        $resourceKind = $this->createMock(ResourceKind::class);
        $command = new ResourceTransitionCommand(
            $resource,
            new ResourceContents([]),
            SystemTransition::UPDATE()->toTransition($resourceKind, $resource),
            $this->user
        );
        $this->fileHelper->expects($this->once())->method('moveFilesToDestinationPaths');
        $this->handler->handle($command);
    }

    public function testMovingFilesWhenCreatingResource() {
        $fileBaseMetadataId = 1;
        $resourceKind = $this->createResourceKindMock(
            1,
            'books',
            [$this->createMetadataMock(11, $fileBaseMetadataId, MetadataControl::FILE()),]
        );
        $contents = ResourceContents::fromArray([$fileBaseMetadataId => []]);
        $resource = new ResourceEntity($resourceKind, ResourceContents::empty());
        $command = new ResourceTransitionCommand(
            $resource,
            $contents,
            SystemTransition::CREATE()->toTransition($resourceKind),
            $this->user
        );
        $this->fileHelper->expects($this->once())->method('moveFilesToDestinationPaths');
        $this->handler->handle($command);
    }

    public function testCreatingResourceWithWorkflow() {
        $workflow = $this->createMock(ResourceWorkflow::class);
        $resourceKind = $this->createResourceKindMock(1, 'books', [], $workflow);
        $command = new ResourceTransitionCommand(
            new ResourceEntity($resourceKind, ResourceContents::empty()),
            ResourceContents::fromArray(['1' => ['AA']]),
            SystemTransition::CREATE()->toTransition($resourceKind),
            $this->user
        );
        $resource = $this->handler->handle($command);
        $this->assertNotNull($resource);
        $this->assertEquals($workflow, $resource->getWorkflow());
        $this->assertEquals('books', $resource->getResourceClass());
    }

    public function testCreatingResourceWithoutWorkflow() {
        $resourceKind = $this->createResourceKindMock();
        $contents = ResourceContents::fromArray(['1' => ['AA']]);
        $command = new ResourceTransitionCommand(
            new ResourceEntity($resourceKind, ResourceContents::empty()),
            $contents,
            SystemTransition::CREATE()->toTransition($resourceKind),
            $this->user
        );
        $resource = $this->handler->handle($command);
        $this->assertNotNull($resource);
        $this->assertSame($command->getResource()->getKind(), $resource->getKind());
        $this->assertEquals($contents, $resource->getContents());
        $this->assertSame('books', $resource->getResourceClass());
    }

    public function testCreatedResourceApplyTransitionOnWorkflow() {
        $workflow = $this->createMock(ResourceWorkflow::class);
        $resourceKind = $this->createResourceKindMock(1, 'books', [], $workflow);
        $resource = $this->createMock(ResourceEntity::class);
        $resource->expects($this->once())->method('hasWorkflow')->willReturn(true);
        $resource->expects($this->once())->method('applyTransition')->with('create');
        $command = new ResourceTransitionCommand(
            $resource,
            ResourceContents::empty(),
            SystemTransition::CREATE()->toTransition($resourceKind),
            $this->user
        );
        $this->handler->handle($command);
    }

    public function testPassIfTransitionIdGiven() {
        $transition = $this->createWorkflowTransitionMock([], [], [], 't1');
        $command = new ResourceTransitionCommand($this->resource, ResourceContents::fromArray(['1' => ['AA']]), $transition, $this->user);
        $resource = $this->handler->handle($command);
        $this->assertEquals($this->resource, $resource);
    }

    public function testPassIfResourceWorkflowTransitionGiven() {
        $resourceWorkflowTransition = $this->createMock(ResourceWorkflowTransition::class);
        $resourceWorkflowTransition->expects($this->once())->method('getId');
        $command = new ResourceTransitionCommand(
            $this->resource,
            ResourceContents::fromArray(['1' => ['AA']]),
            $resourceWorkflowTransition,
            $this->user
        );
        $resource = $this->handler->handle($command);
        $this->assertEquals($this->resource, $resource);
    }
}
