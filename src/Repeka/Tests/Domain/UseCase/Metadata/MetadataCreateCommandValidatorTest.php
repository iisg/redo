<?php
namespace Repeka\Tests\Domain\UseCase\Metadata;

use Repeka\Domain\Exception\InvalidCommandException;
use Repeka\Domain\Repository\LanguageRepository;
use Repeka\Domain\UseCase\Metadata\MetadataCreateCommand;
use Repeka\Domain\UseCase\Metadata\MetadataCreateCommandValidator;

class MetadataCreateCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    /** @var MetadataCreateCommandValidator */
    private $validator;

    protected function setUp() {
        $repository = $this->createMock(LanguageRepository::class);
        $repository->expects($this->once())->method('getAvailableLanguageCodes')->willReturn(['PL']);
        $this->validator = new MetadataCreateCommandValidator($repository, ['text', 'textarea']);
    }

    public function testPassingValidation() {
        $command = new MetadataCreateCommand('nazwa', ['PL' => 'Test'], [], [], 'text');
        $this->validator->validate($command);
    }

    /** @expectedException Repeka\Domain\Exception\InvalidCommandException */
    public function testFailsValidationBecauseOfInvalidControl() {
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

    /** @expectedException Repeka\Domain\Exception\InvalidCommandException */
    public function testFailsValidationBecauseOfNoLabelInMainLanguage() {
        $command = new MetadataCreateCommand('nazwa', ['EN' => 'Test'], [], [], 'text');
        $this->validator->validate($command);
    }

    /** @expectedException Repeka\Domain\Exception\InvalidCommandException */
    public function testFailsValidationBecauseThereIsNoName() {
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
