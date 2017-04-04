<?php
namespace Repeka\Tests\Domain\UseCase\Metadata;

use Repeka\Domain\Exception\InvalidCommandException;
use Repeka\Domain\Repository\LanguageRepository;
use Repeka\Domain\UseCase\Metadata\MetadataCreateCommand;
use Repeka\Domain\UseCase\Metadata\MetadataCreateCommandValidator;
use Repeka\Domain\Validation\Rules\ContainsOnlyAvailableLanguagesRule;
use Repeka\Domain\Validation\Rules\IsValidControlRule;
use Repeka\Domain\Validation\Rules\NotBlankInAllLanguagesRule;
use Repeka\Tests\Traits\StubsTrait;

class MetadataCreateCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var MetadataCreateCommandValidator */
    private $validator;

    protected function setUp() {
        $languageRepositoryStub = $this->createMock(LanguageRepository::class);
        $languageRepositoryStub->expects($this->atLeastOnce())->method('getAvailableLanguageCodes')->willReturn(['PL']);
        $this->validator = new MetadataCreateCommandValidator(
            new NotBlankInAllLanguagesRule($languageRepositoryStub),
            new ContainsOnlyAvailableLanguagesRule($languageRepositoryStub),
            new IsValidControlRule(['text', 'textarea'])
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
            $this->assertEquals('control', $e->getData()[0]['field']);
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

    public function testTellsThatLabelIsInvalid() {
        $command = new MetadataCreateCommand('nazwa', [], [], [], 'text');
        try {
            $this->validator->validate($command);
        } catch (InvalidCommandException $e) {
            $this->assertEquals('label', $e->getData()[0]['field']);
        }
    }
}
