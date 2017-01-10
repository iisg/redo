<?php
namespace Repeka\Tests\Domain\Validation\Rules;

use Repeka\Domain\Validation\Validator;

class ContainsOnlyAvailableLanguagesTest extends \PHPUnit_Framework_TestCase {
    public function testValidatorIsRegistered() {
        Validator::containsOnlyAvailableLanguages(['PL', 'EN']);
    }

    public function testPassesValidation() {
        $this->assertTrue(Validator::containsOnlyAvailableLanguages(['PL', 'EN'])->validate(['PL' => 'value', 'EN' => 'value']));
    }

    public function testPassesValidationWhenLanugageMissing() {
        $this->assertTrue(Validator::containsOnlyAvailableLanguages(['PL', 'EN'])->validate(['PL' => 'value']));
    }

    public function testPassesValidationWhenEmptyArray() {
        $this->assertTrue(Validator::containsOnlyAvailableLanguages(['PL', 'EN'])->validate([]));
    }

    public function testFailsValidationWhenNotArray() {
        $this->assertFalse(Validator::containsOnlyAvailableLanguages(['PL', 'EN'])->validate('aa'));
    }

    public function testFailsValidationWhenExtraLanguage() {
        $this->assertFalse(Validator::containsOnlyAvailableLanguages(['PL', 'EN'])->validate(['PL' => 'value', 'XX' => 'value']));
    }
}
