<?php
namespace Repeka\Tests\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowUpdateCommand;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowUpdateCommandValidator;
use Repeka\Domain\Validation\Rules\NotBlankInAllLanguagesRule;
use Repeka\Tests\Traits\StubsTrait;

class ResourceWorkflowUpdateCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var \PHPUnit_Framework_MockObject_MockObject|ResourceWorkflow */
    private $workflow;
    /** @var ResourceWorkflowUpdateCommandValidator */
    private $validator;

    protected function setUp() {
        $entityExistsRule = $this->createEntityExistsMock(true);
        $notBlankInAllLanguagesRule = $this->createRuleMock(NotBlankInAllLanguagesRule::class, true);
        $this->validator = new ResourceWorkflowUpdateCommandValidator($entityExistsRule, $notBlankInAllLanguagesRule);
        $this->workflow = $this->createMock(ResourceWorkflow::class);
        $this->workflow->expects($this->any())->method('getId')->willReturn(1);
    }

    public function testValid() {
        $command = new ResourceWorkflowUpdateCommand($this->workflow, [], [['label' => []]], [], null, null);
        $this->validator->validate($command);
    }

    public function testInvalidWhenNoPlaces() {
        $command = new ResourceWorkflowUpdateCommand($this->workflow, [], [], [], null, null);
        $this->assertFalse($this->validator->isValid($command));
    }

    public function testInvalidBecauseOfNotSavedWorkflow() {
        $command = new ResourceWorkflowUpdateCommand($this->createMock(ResourceWorkflow::class), [], [], [], null, null);
        $this->assertFalse($this->validator->isValid($command));
    }
}
