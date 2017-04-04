<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\ResourceWorkflowTransition;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Exception\InvalidCommandException;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommandValidator;

class ResourceTransitionCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    /** @var  ResourceWorkflow|PHPUnit_Framework_MockObject_MockObject */
    private $workflow;
    /** @var  ResourceEntity|PHPUnit_Framework_MockObject_MockObject */
    private $resource;
    /** @var ResourceTransitionCommandValidator */
    private $validator;

    protected function setUp() {
        $this->workflow = $this->createMock(ResourceWorkflow::class);
        $this->resource = $this->createMock(ResourceEntity::class);
        $this->validator = new ResourceTransitionCommandValidator();
        $this->resource->expects($this->any())->method('getWorkflow')->willReturn($this->workflow);
    }

    public function testInvalidWhenEmptyTransitionId() {
        $this->expectException(InvalidCommandException::class);
        $this->expectExceptionMessageRegExp('/blank/');
        $this->resource->expects($this->once())->method('getId')->willReturn(1);
        $command = new ResourceTransitionCommand($this->resource, '', $this->createMock(User::class));
        $this->validator->validate($command);
    }

    public function testInvalidWhenNotSavedResourceKind() {
        $this->expectException(InvalidCommandException::class);
        $this->expectExceptionMessageRegExp('/greater than 0/');
        $this->resource->expects($this->once())->method('hasWorkflow')->willReturn(true);
        $command = new ResourceTransitionCommand($this->resource, 't1', $this->createMock(User::class));
        $this->validator->validate($command);
    }

    public function testInvalidWhenNoWorkflow() {
        $this->expectException(InvalidCommandException::class);
        $this->expectExceptionMessageRegExp('/workflow/');
        $this->resource->expects($this->once())->method('getId')->willReturn(1);
        $this->resource->expects($this->once())->method('hasWorkflow')->willReturn(false);
        $command = new ResourceTransitionCommand($this->resource, 't1', $this->createMock(User::class));
        $this->validator->validate($command);
    }

    public function testInvalidWhenInvalidTransition() {
        $this->expectException(InvalidCommandException::class);
        $this->expectExceptionMessageRegExp('/transitionId/');
        $this->resource->expects($this->once())->method('getId')->willReturn(1);
        $this->resource->expects($this->once())->method('hasWorkflow')->willReturn(true);
        $this->workflow->expects($this->once())->method('getTransitions')
            ->willReturn([new ResourceWorkflowTransition([], [], [], [], 't1')]);
        $command = new ResourceTransitionCommand($this->resource, 't2', $this->createMock(User::class));
        $this->validator->validate($command);
    }

    public function testValid() {
        $this->resource->expects($this->once())->method('getId')->willReturn(1);
        $this->resource->expects($this->once())->method('hasWorkflow')->willReturn(true);
        $this->workflow->expects($this->once())->method('getTransitions')
            ->willReturn([new ResourceWorkflowTransition([], [], [], [], 't1')]);
        $command = new ResourceTransitionCommand($this->resource, 't1', $this->createMock(User::class));
        $this->validator->validate($command);
    }
}
