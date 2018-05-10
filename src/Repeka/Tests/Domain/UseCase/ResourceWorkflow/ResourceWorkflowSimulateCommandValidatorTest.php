<?php
namespace Repeka\Tests\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowSimulateCommand;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowSimulateCommandValidator;
use Repeka\Domain\Validation\Rules\NotBlankInAllLanguagesRule;
use Repeka\Domain\Validation\Rules\WorkflowPlacesDefinitionIsValidRule;
use Repeka\Domain\Validation\Rules\WorkflowPlacesForDeletionAreUnoccupiedRule;
use Repeka\Domain\Validation\Rules\WorkflowTransitionNamesMatchInAllLanguagesRule;
use Repeka\Domain\Validation\Rules\WorkflowTransitionsDefinitionIsValidRule;
use Repeka\Tests\Traits\StubsTrait;

/** @SuppressWarnings("PHPMD.LongVariable") */
class ResourceWorkflowSimulateCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceWorkflowSimulateCommandValidator */
    private $validator;

    protected function setUp() {
        $entityExistsRule = $this->createEntityExistsMock(true);
        $notBlankInAllLanguagesRule = $this->createRuleMock(NotBlankInAllLanguagesRule::class, true);
        $workflowPlacesDefinitionIsValidRule = new WorkflowPlacesDefinitionIsValidRule($entityExistsRule);
        $workflowTransitionsDefinitionIsValidRule = $this->createRuleMock(WorkflowTransitionsDefinitionIsValidRule::class, true);
        $workflowPlacesForDeletionAreUnoccupiedRule = $this->createRuleMock(WorkflowPlacesForDeletionAreUnoccupiedRule::class, true);
        $workflowTransitionNamesMatchInAllLanguagesRule =
            $this->createRuleWithFactoryMethodMock(WorkflowTransitionNamesMatchInAllLanguagesRule::class, "withPlaces", true);
        $this->validator = new ResourceWorkflowSimulateCommandValidator(
            $entityExistsRule,
            $notBlankInAllLanguagesRule,
            $workflowTransitionsDefinitionIsValidRule,
            $workflowPlacesDefinitionIsValidRule,
            $workflowPlacesForDeletionAreUnoccupiedRule,
            $workflowTransitionNamesMatchInAllLanguagesRule
        );
    }

    public function testValid() {
        $command = new ResourceWorkflowSimulateCommand([['label' => []]], []);
        $this->validator->validate($command);
    }
}
