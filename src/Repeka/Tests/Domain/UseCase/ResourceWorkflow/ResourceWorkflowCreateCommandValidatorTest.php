<?php
namespace Repeka\Tests\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Exception\InvalidCommandException;
use Repeka\Domain\Repository\LanguageRepository;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowCreateCommand;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowCreateCommandValidator;

class ResourceWorkflowCreateCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    /** @var ResourceWorkflowCreateCommandValidator */
    private $validator;

    protected function setUp() {
        $repository = $this->createMock(LanguageRepository::class);
        $repository->expects($this->once())->method('getAvailableLanguageCodes')->willReturn(['PL']);
        $this->validator = new ResourceWorkflowCreateCommandValidator($repository);
    }

    public function testPassingValidation() {
        $command = new ResourceWorkflowCreateCommand(['PL' => 'Test']);
        $this->validator->validate($command);
    }

    public function testFailsValidationBecauseOfInvalidName() {
        $this->expectException(InvalidCommandException::class);
        $command = new ResourceWorkflowCreateCommand([]);
        $this->validator->validate($command);
    }
}
