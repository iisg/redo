<?php
namespace Repeka\Tests\Domain\Validation\Rules;

use Repeka\Domain\Validation\Rules\NotBlankInAllLanguagesRule;
use Repeka\Tests\Traits\StubsTrait;

class NotBlankInAllLanguagesRuleTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    private function createTestSubject(array $languages) {
        $repository = $this->createLanguageRepositoryMock($languages);
        return new NotBlankInAllLanguagesRule($repository);
    }

    public function testPassesValidation() {
        $this->assertTrue($this->createTestSubject(['PL', 'EN'])->validate(['PL' => 'value', 'EN' => 'value']));
    }

    public function testFailsValidationWhenBlank() {
        $this->assertFalse($this->createTestSubject(['PL', 'EN'])->validate(['PL' => '    ', 'EN' => 'value']));
    }

    public function testFailsValidationWhenNotAllLanguagesProvided() {
        $this->assertFalse($this->createTestSubject(['PL', 'EN', 'RUS'])->validate(['PL' => 'value', 'EN' => 'value']));
    }

    public function testFailsValidationWhenOtherLanguage() {
        $this->assertFalse($this->createTestSubject(['PL', 'EN'])->validate(['PL' => 'value', 'EN' => 'value', 'GER' => 'value']));
    }

    public function testFailsWhenNotArrayGiven() {
        $this->assertFalse($this->createTestSubject(['PL', 'EN'])->validate('PL'));
    }
}
