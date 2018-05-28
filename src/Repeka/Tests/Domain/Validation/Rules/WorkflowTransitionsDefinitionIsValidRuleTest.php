<?php
namespace Repeka\Tests\Domain\Validation\Rules;

use Repeka\Domain\Entity\Workflow\ResourceWorkflowTransition;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowCreateCommand;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowCreateCommandValidator;
use Repeka\Domain\Validation\Rules\NoAssigneeMetadataInFirstPlaceRule;
use Repeka\Domain\Validation\Rules\NotBlankInAllLanguagesRule;
use Repeka\Domain\Validation\Rules\ResourceClassExistsRule;
use Repeka\Domain\Validation\Rules\WorkflowPlacesDefinitionIsValidRule;
use Repeka\Domain\Validation\Rules\WorkflowTransitionNamesMatchInAllLanguagesRule;
use Repeka\Domain\Validation\Rules\WorkflowTransitionsDefinitionIsValidRule;
use Repeka\Tests\Traits\StubsTrait;

/** @SuppressWarnings("PHPMD.LongVariable") */
class WorkflowTransitionsDefinitionIsValidRuleTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceWorkflowCreateCommandValidator */
    private $validator;

    protected function setUp() {
        $entityExistsRule = $this->createEntityExistsMock(true);
        $notBlankInAllLanguagesRule = $this->createRuleMock(NotBlankInAllLanguagesRule::class, true);
        $resourceClassExistsRule = $this->createRuleMock(ResourceClassExistsRule::class, true);
        $workflowPlacesDefinitionIsValidRule = new WorkflowPlacesDefinitionIsValidRule($entityExistsRule);
        $workflowTransitionsDefinitionIsValidRule = $this->createRuleMock(WorkflowTransitionsDefinitionIsValidRule::class, true);
        $workflowTransitionNamesMatchInAllLanguagesRule =
            $this->createRuleWithFactoryMethodMock(WorkflowTransitionNamesMatchInAllLanguagesRule::class, "withPlaces", true);
        $noAssigneeMetadataInFirstPlaceRule = $this->createRuleMock(NoAssigneeMetadataInFirstPlaceRule::class, true);
        $this->validator = new ResourceWorkflowCreateCommandValidator(
            $notBlankInAllLanguagesRule,
            $resourceClassExistsRule,
            $workflowTransitionsDefinitionIsValidRule,
            $workflowPlacesDefinitionIsValidRule,
            $workflowTransitionNamesMatchInAllLanguagesRule,
            $noAssigneeMetadataInFirstPlaceRule
        );
    }

    public function testValidWithTransitionsAsArray() {
        $command =
            new ResourceWorkflowCreateCommand([], [['label' => []]], [['label' => [], 'froms' => [], 'tos' => []]], 'books', null, null);
        $this->validator->validate($command);
    }

    public function testValidWithTransitionsWithRoles() {
        $command = new ResourceWorkflowCreateCommand(
            [],
            [['label' => []]],
            [['label' => [], 'froms' => [], 'tos' => [], 'permittedRoleIds' => ['AAA']]],
            'books',
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
            'books',
            null,
            null
        );
        $this->validator->validate($command);
    }

    public function testValidWithTransitionsAsObjects() {
        $command =
            new ResourceWorkflowCreateCommand([], [['label' => []]], [new ResourceWorkflowTransition([], [], [])], 'books', null, null);
        $this->validator->validate($command);
    }

    public function testInvalidWhenLackOfTransitionData() {
        $command = new ResourceWorkflowCreateCommand([], [], [['label' => [], 'froms' => []]], 'books', null, null);
        $this->assertFalse($this->validator->isValid($command));
    }
}
