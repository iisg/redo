<?php
namespace Repeka\Tests\Domain\UseCase\Language;

use Repeka\Domain\Exception\InvalidCommandException;
use Repeka\Domain\UseCase\Language\LanguageCreateCommand;
use Repeka\Domain\UseCase\Language\LanguageCreateCommandValidator;

class LanguageCreateCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    /** @var LanguageCreateCommandValidator */
    private $validator;

    protected function setUp() {
        $this->validator = new LanguageCreateCommandValidator();
    }

    public function testPassingValidation() {
        $command = new LanguageCreateCommand('CA-FR', 'PL', 'polski');
        $this->validator->validate($command);
    }

    /**
     * @expectedException Repeka\Domain\Exception\InvalidCommandException
     */
    public function testFailsValidationBecauseUnderscore() {
        $command = new LanguageCreateCommand('CA_FR', 'PL', 'polski');
        $this->validator->validate($command);
    }

    /**
     * @expectedException Repeka\Domain\Exception\InvalidCommandException
     */
    public function testFailsValidationBecauseNoUpperCase() {
        $command = new LanguageCreateCommand('fr', 'PL', 'polski');
        $this->validator->validate($command);
    }

    /**
     * @expectedException Repeka\Domain\Exception\InvalidCommandException
     */
    public function testFailsValidationBecauseOfNoCode() {
        $command = new LanguageCreateCommand('', 'PL', 'polski');
        $this->validator->validate($command);
    }

    /**
     * @expectedException Repeka\Domain\Exception\InvalidCommandException
     */
    public function testFailsValidationBecauseOfNoFlag() {
        $command = new LanguageCreateCommand('CA-FR', '', 'polski');
        $this->validator->validate($command);
    }

    /**
     * @expectedException Repeka\Domain\Exception\InvalidCommandException
     */
    public function testFailsValidationBecauseOfNoLanguageName() {
        $command = new LanguageCreateCommand('CA-FR', 'PL', '');
        $this->validator->validate($command);
    }

    /**
     * @expectedException Repeka\Domain\Exception\InvalidCommandException
     */
    public function testFailsValidationBecauseOfMoreThan10CharactersInCodeUsed() {
        $command = new LanguageCreateCommand('AAAAA-AAAAA', 'PL', 'polski');
        $this->validator->validate($command);
    }

    /**
     * @expectedException Repeka\Domain\Exception\InvalidCommandException
     */
    public function testFailsValidationBecauseOfLessThan2CharactersInCodeUsed() {
        $command = new LanguageCreateCommand('A', 'PL', 'polski');
        $this->validator->validate($command);
    }
}
