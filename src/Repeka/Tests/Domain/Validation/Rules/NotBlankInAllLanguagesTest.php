<?php
namespace Repeka\Tests\Domain\Validation\Rules;

use Repeka\Domain\Validation\Validator;

class NotBlankInAllLanguagesTest extends \PHPUnit_Framework_TestCase {
    public function testValidatorIsRegistered() {
        Validator::notBlankInAllLanguages(['PL', 'EN']);
    }

    public function testPassesValidation() {
        $this->assertTrue(Validator::notBlankInAllLanguages(['PL', 'EN'])->validate(['PL' => 'value', 'EN' => 'value']));
    }

    public function testFailsValidationWhenBlank() {
        $this->assertFalse(Validator::notBlankInAllLanguages(['PL', 'EN'])->validate(['PL' => '    ', 'EN' => 'value']));
    }

    public function testFailsValidationWhenNotAllLanguagesProvided() {
        $this->assertFalse(Validator::notBlankInAllLanguages(['PL', 'EN', 'RUS'])->validate(['PL' => 'value', 'EN' => 'value']));
    }

    public function testFailsValidationWhenOtherLanguage() {
        $this->assertFalse(Validator::notBlankInAllLanguages(['PL', 'EN'])->validate(['PL' => 'value', 'EN' => 'value', 'GER' => 'value']));
    }

    public function testFailsWhenNotArrayGiven() {
        $this->assertFalse(Validator::notBlankInAllLanguages(['PL', 'EN'])->validate('PL'));
    }
}
