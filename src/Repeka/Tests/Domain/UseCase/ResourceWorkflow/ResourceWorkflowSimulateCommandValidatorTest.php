<?php
namespace Repeka\Tests\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowSimulateCommand;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowSimulateCommandValidator;
use Repeka\Domain\Validation\Rules\NotBlankInAllLanguagesRule;
use Repeka\Tests\Traits\StubsTrait;

class ResourceWorkflowSimulateCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceWorkflowSimulateCommandValidator */
    private $validator;

    protected function setUp() {
        $entityExistsRule = $this->createEntityExistsMock(true);
        $notBlankInAllLanguagesRule = $this->createRuleMock(NotBlankInAllLanguagesRule::class, true);
        $this->validator = new ResourceWorkflowSimulateCommandValidator($entityExistsRule, $notBlankInAllLanguagesRule);
    }

    public function testValid() {
        $command = new ResourceWorkflowSimulateCommand([['label' => []]], []);
        $this->validator->validate($command);
    }

    public function testInvalidIfNoPlaces() {
        $command = new ResourceWorkflowSimulateCommand([], []);
        $this->assertFalse($this->validator->isValid($command));
    }

    public function testInvalidIfInvalidPlace() {
        $command = new ResourceWorkflowSimulateCommand([[]], []);
        $this->assertFalse($this->validator->isValid($command));
    }
}
