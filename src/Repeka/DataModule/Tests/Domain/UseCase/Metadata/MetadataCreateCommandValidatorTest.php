<?php
namespace Repeka\DataModule\Tests\Domain\UseCase\Metadata;

use Repeka\CoreModule\Domain\Exception\InvalidCommandException;
use Repeka\DataModule\Domain\UseCase\Metadata\MetadataCreateCommand;
use Repeka\DataModule\Domain\UseCase\Metadata\MetadataCreateCommandValidator;

class MetadataCreateCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    /** @var MetadataCreateCommandValidator */
    private $validator;

    protected function setUp() {
        $this->validator = new MetadataCreateCommandValidator('PL', ['text', 'textarea']);
    }

    public function testPassingValidation() {
        $command = new MetadataCreateCommand('nazwa', ['PL' => 'Test'], [], [], 'text');
        $this->validator->validate($command);
    }

    /**
     * @expectedException Repeka\CoreModule\Domain\Exception\InvalidCommandException
     */
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

    /**
     * @expectedException Repeka\CoreModule\Domain\Exception\InvalidCommandException
     */
    public function testFailsValidationBecauseOfNoLabelInMainLanguage() {
        $command = new MetadataCreateCommand('nazwa', ['EN' => 'Test'], [], [], 'text');
        $this->validator->validate($command);
    }

    /**
     * @expectedException Repeka\CoreModule\Domain\Exception\InvalidCommandException
     */
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
