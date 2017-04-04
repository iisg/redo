<?php
namespace Repeka\Tests\Domain\UseCase\Metadata;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Exception\InvalidCommandException;
use Repeka\Domain\UseCase\Metadata\MetadataChildCreateCommand;
use Repeka\Domain\UseCase\Metadata\MetadataChildCreateCommandValidator;
use Repeka\Domain\Validation\Rules\ContainsOnlyAvailableLanguagesRule;
use Repeka\Domain\Validation\Rules\IsValidControlRule;
use Repeka\Domain\Validation\Rules\NotBlankInAllLanguagesRule;
use Repeka\Tests\Traits\StubsTrait;

class MetadataChildCreateCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var Metadata */
    private $parent;
    /** @var MetadataChildCreateCommandValidator */
    private $validator;

    protected function setUp() {
        $this->parent = $this->createMock(Metadata::class);
        $languageRepositoryStub = $this->createLanguageRepositoryMock(['PL', 'EN']);
        $this->validator = new MetadataChildCreateCommandValidator(
            new NotBlankInAllLanguagesRule($languageRepositoryStub),
            new ContainsOnlyAvailableLanguagesRule($languageRepositoryStub),
            new IsValidControlRule(['text', 'textarea'])
        );
    }

    public function testPassingValidation() {
        $command = new MetadataChildCreateCommand($this->parent, [
            'name' => 'nazwa',
            'label' => ['PL' => 'TestPL', 'EN' => 'testEN'],
            'placeholder' => [],
            'description' => [],
            'control' => 'text'
        ]);
        $this->validator->validate($command);
    }

    public function testFailsValidationWithInvalidControl() {
        $this->expectException(InvalidCommandException::class);
        $command = new MetadataChildCreateCommand($this->parent, [
            'name' => 'nazwa',
            'label' => ['PL' => 'TestPL', 'EN' => 'testEN'],
            'placeholder' => [],
            'description' => [],
            'control' => 'blabla'
        ]);
        $this->validator->validate($command);
    }

    public function testFailsValidationWhenLabelIsMissingInSomeLanguage() {
        $this->expectException(InvalidCommandException::class);
        $command = new MetadataChildCreateCommand($this->parent, [
            'name' => 'nazwa',
            'label' => ['EN' => 'Test'],
            'placeholder' => [],
            'description' => [],
            'control' => 'text'
        ]);
        $this->validator->validate($command);
    }

    public function testFailsValidationWhenNameIsEmpty() {
        $this->expectException(InvalidCommandException::class);
        $command = new MetadataChildCreateCommand($this->parent, [
            'name' => '',
            'label' => ['PL' => 'TestPL', 'EN' => 'testEN'],
            'placeholder' => [],
            'description' => [],
            'control' => 'text'
        ]);
        $this->validator->validate($command);
    }
}
