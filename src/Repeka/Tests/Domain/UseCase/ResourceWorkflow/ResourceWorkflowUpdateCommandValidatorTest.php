<?php
namespace Repeka\Tests\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\ResourceWorkflowPlace;
use Repeka\Domain\Entity\ResourceWorkflowTransition;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowUpdateCommand;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowUpdateCommandValidator;

class ResourceWorkflowUpdateCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    /** @var \PHPUnit_Framework_MockObject_MockObject|ResourceWorkflow */
    private $workflow;
    /** @var ResourceWorkflowUpdateCommandValidator */
    private $validator;

    protected function setUp() {
        $this->validator = new ResourceWorkflowUpdateCommandValidator();
        $this->workflow = $this->createMock(ResourceWorkflow::class);
        $this->workflow->expects($this->any())->method('getId')->willReturn(1);
    }

    public function testValid() {
        $command = new ResourceWorkflowUpdateCommand($this->workflow, [], [], null, null);
        $this->validator->validate($command);
    }

    public function testInvalidBecauseOfNotSavedWorkflow() {
        $command = new ResourceWorkflowUpdateCommand(new ResourceWorkflow([]), [], [], null, null);
        $this->assertFalse($this->validator->isValid($command));
    }

    public function testValidWithPlaceAsArray() {
        $command = new ResourceWorkflowUpdateCommand($this->workflow, [['label' => ['PL' => 'A']]], [], null, null);
        $this->validator->validate($command);
    }

    public function testValidWithPlaceAsArrayWithIdentifier() {
        $command = new ResourceWorkflowUpdateCommand($this->workflow, [['id' => '123', 'label' => []]], [], null, null);
        $this->validator->validate($command);
    }

    public function testValidWithPlaceAsObjects() {
        $command = new ResourceWorkflowUpdateCommand($this->workflow, [new ResourceWorkflowPlace([])], [], null, null);
        $this->validator->validate($command);
    }

    public function testInvalidWithInvalidPlace() {
        $command = new ResourceWorkflowUpdateCommand($this->workflow, [[]], [], null, null);
        $this->assertFalse($this->validator->isValid($command));
    }

    public function testInvalidWithExtraDataInPlace() {
        $command = new ResourceWorkflowUpdateCommand($this->workflow, [['label' => [], 'extra' => '']], [], null, null);
        $this->assertFalse($this->validator->isValid($command));
    }

    public function testInvalidWithLabelNotArray() {
        $command = new ResourceWorkflowUpdateCommand($this->workflow, [['label' => '']], [], null, null);
        $this->assertFalse($this->validator->isValid($command));
    }

    public function testValidWithTransitionsAsArray() {
        $command = new ResourceWorkflowUpdateCommand($this->workflow, [], [['label' => [], 'froms' => [], 'tos' => []]], null, null);
        $this->validator->validate($command);
    }

    public function testValidWithTransitionsWithRoles() {
        $command = new ResourceWorkflowUpdateCommand(
            $this->workflow,
            [],
            [['label' => [], 'froms' => [], 'tos' => [], 'permittedRoleIds' => ['AAA']]],
            null,
            null
        );
        $this->validator->validate($command);
    }

    public function testValidWithTransitionsWithEmptyRoles() {
        $command = new ResourceWorkflowUpdateCommand(
            $this->workflow,
            [],
            [['label' => [], 'froms' => [], 'tos' => [], 'permittedRoleIds' => []]],
            null,
            null
        );
        $this->validator->validate($command);
    }

    public function testValidWithTransitionsAsObjects() {
        $command = new ResourceWorkflowUpdateCommand($this->workflow, [], [new ResourceWorkflowTransition([], [], [])], null, null);
        $this->validator->validate($command);
    }

    public function testInvalidWhenLackOfTransitionData() {
        $command = new ResourceWorkflowUpdateCommand($this->workflow, [], [['label' => [], 'froms' => []]], null, null);
        $this->assertFalse($this->validator->isValid($command));
    }
}
