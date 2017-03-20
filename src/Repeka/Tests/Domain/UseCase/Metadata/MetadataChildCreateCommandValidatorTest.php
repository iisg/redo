<?php
namespace Repeka\Tests\Domain\UseCase\Metadata;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Exception\InvalidCommandException;
use Repeka\Domain\Repository\LanguageRepository;
use Repeka\Domain\UseCase\Metadata\MetadataChildCreateCommand;
use Repeka\Domain\UseCase\Metadata\MetadataChildCreateCommandValidator;

class MetadataChildCreateCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    /** @var Metadata */
    private $parent;
    /** @var MetadataChildCreateCommandValidator */
    private $validator;

    protected function setUp() {
        $this->parent = $this->createMock(Metadata::class);
        $repository = $this->createMock(LanguageRepository::class);
        $repository->expects($this->once())->method('getAvailableLanguageCodes')->willReturn(['PL', 'EN']);
        $this->validator = new MetadataChildCreateCommandValidator($repository, ['text', 'textarea']);
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

    public function testFailsValidationBecauseOfInvalidControl() {
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

    public function testFailsValidationBecauseOfLabelIsMissingInSomeLanguage() {
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

    public function testFailsValidationBecauseNameIsEmpty() {
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
