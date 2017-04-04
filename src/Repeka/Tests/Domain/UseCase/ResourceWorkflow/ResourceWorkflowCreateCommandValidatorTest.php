<?php
namespace Repeka\Tests\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Exception\InvalidCommandException;
use Repeka\Domain\Repository\LanguageRepository;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowCreateCommand;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowCreateCommandValidator;
use Repeka\Domain\Validation\Rules\NotBlankInAllLanguagesRule;
use Repeka\Tests\Traits\StubsTrait;

class ResourceWorkflowCreateCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceWorkflowCreateCommandValidator */
    private $validator;

    protected function setUp() {
        $languageRepositoryStub = $this->createMock(LanguageRepository::class);
        $languageRepositoryStub->expects($this->once())->method('getAvailableLanguageCodes')->willReturn(['PL']);
        $this->validator = new ResourceWorkflowCreateCommandValidator(new NotBlankInAllLanguagesRule($languageRepositoryStub));
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
