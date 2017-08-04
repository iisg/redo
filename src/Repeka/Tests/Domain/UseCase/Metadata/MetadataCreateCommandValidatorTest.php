<?php
namespace Repeka\Tests\Domain\UseCase\Metadata;

use Repeka\Domain\Exception\InvalidCommandException;
use Repeka\Domain\Repository\LanguageRepository;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\UseCase\Metadata\MetadataCreateCommand;
use Repeka\Domain\UseCase\Metadata\MetadataCreateCommandValidator;
use Repeka\Domain\Validation\Rules\ConstraintArgumentsAreValidRule;
use Repeka\Domain\Validation\Rules\ConstraintSetMatchesControlRule;
use Repeka\Domain\Validation\Rules\ContainsOnlyAvailableLanguagesRule;
use Repeka\Domain\Validation\Rules\IsValidControlRule;
use Repeka\Domain\Validation\Rules\NotBlankInAllLanguagesRule;
use Repeka\Tests\Traits\StubsTrait;

class MetadataCreateCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var LanguageRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $languageRepositoryStub;
    /** @var MetadataCreateCommandValidator */
    private $validator;
    /** @var ConstraintArgumentsAreValidRule|\PHPUnit_Framework_MockObject_MockObject */
    private $constraintArgumentsAreValid;

    protected function setUp() {
        $this->languageRepositoryStub = $this->createMock(LanguageRepository::class);
        $this->languageRepositoryStub->expects($this->atLeastOnce())->method('getAvailableLanguageCodes')->willReturn(['PL']);
        $this->constraintArgumentsAreValid = $this->createMock(ConstraintArgumentsAreValidRule::class);
        $this->validator = new MetadataCreateCommandValidator(
            new NotBlankInAllLanguagesRule($this->languageRepositoryStub),
            new ContainsOnlyAvailableLanguagesRule($this->languageRepositoryStub),
            new IsValidControlRule(['text', 'textarea']),
            new ConstraintSetMatchesControlRule($this->createMock(MetadataRepository::class)),
            $this->constraintArgumentsAreValid
        );
    }

    public function testPassingValidation() {
        $command = new MetadataCreateCommand('nazwa', ['PL' => 'Test'], [], [], 'text');
        $this->validator->validate($command);
    }

    public function testFailsValidationBecauseOfInvalidControl() {
        $this->expectException(InvalidCommandException::class);
        $command = new MetadataCreateCommand('nazwa', ['PL' => 'Test'], [], [], 'blabla');
        $this->validator->validate($command);
    }

    public function testTellsThatControlIsInvalid() {
        $command = new MetadataCreateCommand('nazwa', ['PL' => 'Test'], [], [], 'blabla');
        try {
            $this->validator->validate($command);
        } catch (InvalidCommandException $e) {
            $this->assertEquals('control', $e->getViolations()[0]['field']);
        }
    }

    public function testFailsValidationBecauseOfNoLabelInMainLanguage() {
        $this->expectException(InvalidCommandException::class);
        $command = new MetadataCreateCommand('nazwa', ['EN' => 'Test'], [], [], 'text');
        $this->validator->validate($command);
    }

    public function testFailsValidationBecauseThereIsNoName() {
        $this->expectException(InvalidCommandException::class);
        $command = new MetadataCreateCommand('', ['PL' => 'Test'], [], [], 'text');
        $this->validator->validate($command);
    }

    public function testFailsValidationWhenReferencedResourceKindDoesNotExist() {
        $this->expectException(InvalidCommandException::class);
        $this->constraintArgumentsAreValid->method('validate')->willReturn(false);
        $validator = new MetadataCreateCommandValidator(
            new NotBlankInAllLanguagesRule($this->languageRepositoryStub),
            new ContainsOnlyAvailableLanguagesRule($this->languageRepositoryStub),
            new IsValidControlRule(['text', 'textarea']),
            new ConstraintSetMatchesControlRule($this->createMock(MetadataRepository::class)),
            $this->constraintArgumentsAreValid
        );
        $command = new MetadataCreateCommand('nazwa', ['PL' => 'Test'], [], [], 'relationship', ['resourceKind' => [1]]);
        $validator->validate($command);
    }

    public function testValidationSucceedsWhenReferencedResourceKindExists() {
        $this->constraintArgumentsAreValid->method('validate')->willReturn(true);
        $validator = new MetadataCreateCommandValidator(
            new NotBlankInAllLanguagesRule($this->languageRepositoryStub),
            new ContainsOnlyAvailableLanguagesRule($this->languageRepositoryStub),
            new IsValidControlRule(['text', 'textarea', 'relationship']),
            new ConstraintSetMatchesControlRule($this->createMock(MetadataRepository::class)),
            $this->constraintArgumentsAreValid
        );
        $command = new MetadataCreateCommand('nazwa', ['PL' => 'Test'], [], [], 'relationship', ['resourceKind' => [1]]);
        $validator->validate($command);
    }

    public function testTellsThatLabelIsInvalid() {
        $command = new MetadataCreateCommand('nazwa', [], [], [], 'text');
        try {
            $this->validator->validate($command);
        } catch (InvalidCommandException $e) {
            $this->assertEquals('label', $e->getViolations()[0]['field']);
        }
    }
}
