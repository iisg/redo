<?php
namespace Repeka\Tests\Domain\UseCase\Language;

use Repeka\Domain\Exception\InvalidCommandException;
use Repeka\Domain\UseCase\Language\LanguageUpdateCommand;
use Repeka\Domain\UseCase\Language\LanguageUpdateCommandValidator;

class LanguageUpdateCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    /** @var LanguageUpdateCommandValidator */
    private $validator;

    protected function setUp() {
        $this->validator = new LanguageUpdateCommandValidator();
    }

    public function testPassingValidation() {
        $command = LanguageUpdateCommand::fromArray('PL', ['flag' => 'PL', 'name' => 'polski']);
        $this->validator->validate($command);
    }

    public function testFailsValidationWhenNoFlag() {
        $this->expectException(InvalidCommandException::class);
        $command = LanguageUpdateCommand::fromArray('PL', ['flag' => '', 'name' => 'polski']);
        $this->validator->validate($command);
    }

    public function testFailsValidationWhenNoName() {
        $this->expectException(InvalidCommandException::class);
        $command = LanguageUpdateCommand::fromArray('PL', ['flag' => 'PL', 'name' => '']);
        $this->validator->validate($command);
    }
}
