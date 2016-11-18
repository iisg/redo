<?php
namespace Repeka\Tests\Domain\Validation\Rules;

use Repeka\Domain\Validation\Validator;

class NotBlankInLanguageTest extends \PHPUnit_Framework_TestCase {
    public function testValidatorIsRegistered() {
        Validator::notBlankInLanguage('PL');
    }

    public function testPassesValidation() {
        $this->assertTrue(Validator::notBlankInLanguage('PL')->validate(['PL' => 'Some not blank value']));
    }

    public function testFailsValidationWhenBlank() {
        $this->assertFalse(Validator::notBlankInLanguage('PL')->validate(['PL' => '    ']));
    }

    public function testFailsValidationWhenOtherLanguage() {
        $this->assertFalse(Validator::notBlankInLanguage('PL')->validate(['EN' => 'English']));
    }

    public function testFailsWhenNotArrayGiven() {
        $this->assertFalse(Validator::notBlankInLanguage('PL')->validate('PL'));
    }
}
