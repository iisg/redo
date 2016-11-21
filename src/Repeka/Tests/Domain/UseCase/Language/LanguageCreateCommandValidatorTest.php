<?php
namespace Repeka\Tests\Domain\UseCase\Language;

use Repeka\Domain\Exception\InvalidCommandException;
use Repeka\Domain\UseCase\Language\LanguageCreateCommand;
use Repeka\Domain\UseCase\Language\LanguageCreateCommandValidator;

class MetadataCreateCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    /** @var LanguageCreateCommandValidator */
    private $validator;

    protected function setUp() {
        $this->validator = new LanguageCreateCommandValidator();
    }

    public function testPassingValidation() {
        $command = new LanguageCreateCommand('PL', 'polski');
        $this->validator->validate($command);
    }

    /**
     * @expectedException Repeka\Domain\Exception\InvalidCommandException
     */
    public function testFailsValidationBecauseOfNoFlag() {
        $command = new LanguageCreateCommand('', 'polski');
        $this->validator->validate($command);
    }

    /**
     * @expectedException Repeka\Domain\Exception\InvalidCommandException
     */
    public function testFailsValidationBecauseOfNoLanguageName() {
        $command = new LanguageCreateCommand('PL', '');
        $this->validator->validate($command);
    }
}
