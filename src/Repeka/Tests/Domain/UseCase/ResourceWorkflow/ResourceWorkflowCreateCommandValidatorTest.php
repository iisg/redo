<?php
namespace Repeka\Tests\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\ResourceWorkflowPlace;
use Repeka\Domain\Entity\ResourceWorkflowTransition;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowCreateCommand;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowCreateCommandValidator;
use Repeka\Domain\Validation\Rules\NotBlankInAllLanguagesRule;
use Repeka\Tests\Traits\StubsTrait;

class ResourceWorkflowCreateCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var \PHPUnit_Framework_MockObject_MockObject|ResourceWorkflow */
    private $workflow;
    /** @var ResourceWorkflowCreateCommandValidator */
    private $validator;

    protected function setUp() {
        $entityExistsRule = $this->createEntityExistsMock(true);
        $notBlankInAllLanguagesRule = $this->createRuleMock(NotBlankInAllLanguagesRule::class, true);
        $this->validator = new ResourceWorkflowCreateCommandValidator($entityExistsRule, $notBlankInAllLanguagesRule);
        $this->workflow = $this->createMock(ResourceWorkflow::class);
        $this->workflow->expects($this->any())->method('getId')->willReturn(1);
    }

    public function testValid() {
        $command = new ResourceWorkflowCreateCommand([], [['label' => []]], [], null, null);
        $this->validator->validate($command);
    }

    public function testValidWithPlaceAsArray() {
        $command = new ResourceWorkflowCreateCommand([], [['label' => ['PL' => 'A']]], [], null, null);
        $this->validator->validate($command);
    }

    public function testValidWithPlaceAsArrayWithIdentifier() {
        $command = new ResourceWorkflowCreateCommand([], [['id' => 'fooId', 'label' => []]], [], null, null);
        $this->validator->validate($command);
    }

    public function testValidWithPlaceAsArrayWithRequiredMetadataIds() {
        $command = new ResourceWorkflowCreateCommand([], [[
            'id' => 'fooId',
            'label' => ['PL' => 'A'],
            'requiredMetadataIds' => [1, 2, 3],
            'lockedMetadataIds' => [4, 5],
            'assigneeMetadataIds' => [6]
        ]], [], null, null);
        $this->validator->validate($command);
    }

    public function testInvalidWhenAssigneeMetadataIdsDoNotExist() {
        $entityExistsMock = $this->createEntityExistsMock(false);
        $notBlankInAllLanguagesRule = $this->createRuleMock(NotBlankInAllLanguagesRule::class, true);
        $validator = new ResourceWorkflowCreateCommandValidator($entityExistsMock, $notBlankInAllLanguagesRule);
        $command = new ResourceWorkflowCreateCommand([], [[
            'id' => 'fooId',
            'label' => ['PL' => 'A'],
            'assigneeMetadataIds' => [1],
        ]], [], null, null);
        $this->assertFalse($validator->isValid($command));
    }

    public function testValidWithPlaceAsObjects() {
        $command = new ResourceWorkflowCreateCommand([], [new ResourceWorkflowPlace([])], [], null, null);
        $this->validator->validate($command);
    }

    public function testInvalidWithNoPlaces() {
        $command = new ResourceWorkflowCreateCommand([], [], [], null, null);
        $this->assertFalse($this->validator->isValid($command));
    }

    public function testInvalidWithInvalidPlace() {
        $command = new ResourceWorkflowCreateCommand([], [[]], [], null, null);
        $this->assertFalse($this->validator->isValid($command));
    }

    public function testInvalidWithExtraDataInPlace() {
        $command = new ResourceWorkflowCreateCommand([], [['label' => [], 'extra' => '']], [], null, null);
        $this->assertFalse($this->validator->isValid($command));
    }

    public function testInvalidWithLabelNotArray() {
        $command = new ResourceWorkflowCreateCommand([], [['label' => '']], [], null, null);
        $this->assertFalse($this->validator->isValid($command));
    }

    public function testValidWithTransitionsAsArray() {
        $command = new ResourceWorkflowCreateCommand([], [['label' => []]], [['label' => [], 'froms' => [], 'tos' => []]], null, null);
        $this->validator->validate($command);
    }

    public function testValidWithTransitionsWithRoles() {
        $command = new ResourceWorkflowCreateCommand(
            [],
            [['label' => []]],
            [['label' => [], 'froms' => [], 'tos' => [], 'permittedRoleIds' => ['AAA']]],
            null,
            null
        );
        $this->validator->validate($command);
    }

    public function testValidWithTransitionsWithEmptyRoles() {
        $command = new ResourceWorkflowCreateCommand(
            [],
            [['label' => []]],
            [['label' => [], 'froms' => [], 'tos' => [], 'permittedRoleIds' => []]],
            null,
            null
        );
        $this->validator->validate($command);
    }

    public function testValidWithTransitionsAsObjects() {
        $command = new ResourceWorkflowCreateCommand([], [['label' => []]], [new ResourceWorkflowTransition([], [], [])], null, null);
        $this->validator->validate($command);
    }

    public function testInvalidWhenLackOfTransitionData() {
        $command = new ResourceWorkflowCreateCommand([], [], [['label' => [], 'froms' => []]], null, null);
        $this->assertFalse($this->validator->isValid($command));
    }

    public function testInvalidWhenRequiredMetadataIdsNotAnArray() {
        $command = new ResourceWorkflowCreateCommand([], [[
            'id' => 'fooId',
            'label' => ['PL' => 'A'],
            'requiredMetadataIds' => 1,
        ]], [], null, null);
        $this->assertFalse($this->validator->isValid($command));
    }

    public function testInvalidWhenLockedMetadataIdsNotAnArray() {
        $command = new ResourceWorkflowCreateCommand([], [[
            'id' => 'fooId',
            'label' => ['PL' => 'A'],
            'lockedMetadataIds' => 1,
        ]], [], null, null);
        $this->assertFalse($this->validator->isValid($command));
    }

    public function testInvalidWhenRequiredMetadataIdsDoNotExist() {
        $entityExistsMock = $this->createEntityExistsMock(false);
        $notBlankInAllLanguagesRule = $this->createRuleMock(NotBlankInAllLanguagesRule::class, true);
        $validator = new ResourceWorkflowCreateCommandValidator($entityExistsMock, $notBlankInAllLanguagesRule);
        $command = new ResourceWorkflowCreateCommand([], [[
            'id' => 'fooId',
            'label' => ['PL' => 'A'],
            'requiredMetadataIds' => [1],
        ]], [], null, null);
        $this->assertFalse($validator->isValid($command));
    }

    public function testInvalidWhenLockedMetadataIdsDoNotExist() {
        $entityExistsMock = $this->createEntityExistsMock(false);
        $notBlankInAllLanguagesRule = $this->createRuleMock(NotBlankInAllLanguagesRule::class, true);
        $validator = new ResourceWorkflowCreateCommandValidator($entityExistsMock, $notBlankInAllLanguagesRule);
        $command = new ResourceWorkflowCreateCommand([], [[
            'id' => 'fooId',
            'label' => ['PL' => 'A'],
            'lockedMetadataIds' => [1],
        ]], [], null, null);
        $this->assertFalse($validator->isValid($command));
    }

    public function testInvalidWhenNameLanguagesDoNotMatchSystemLanguages() {
        $entityExistsMock = $this->createEntityExistsMock(true);
        $notBlankInAllLanguagesRule = $this->createRuleMock(NotBlankInAllLanguagesRule::class, false);
        $validator = new ResourceWorkflowCreateCommandValidator($entityExistsMock, $notBlankInAllLanguagesRule);
        $command = new ResourceWorkflowCreateCommand([], [], [], null, null);
        $this->assertFalse($validator->isValid($command));
    }
}
