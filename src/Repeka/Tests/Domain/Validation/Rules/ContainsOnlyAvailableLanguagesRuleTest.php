<?php
namespace Repeka\Tests\Domain\Validation\Rules;

use Repeka\Domain\Validation\Rules\ContainsOnlyAvailableLanguagesRule;
use Repeka\Tests\Traits\StubsTrait;

class ContainsOnlyAvailableLanguagesRuleTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ContainsOnlyAvailableLanguagesRule */
    private $rule;

    protected function setUp() {
        $repository = $this->createLanguageRepositoryMock(['PL', 'EN']);
        $this->rule = new ContainsOnlyAvailableLanguagesRule($repository);
    }

    public function testPassesValidation() {
        $this->assertTrue($this->rule->validate(['PL' => 'value', 'EN' => 'value']));
    }

    public function testPassesValidationWhenLanugageMissing() {
        $this->assertTrue($this->rule->validate(['PL' => 'value']));
    }

    public function testPassesValidationWhenEmptyArray() {
        $this->assertTrue($this->rule->validate([]));
    }

    public function testFailsValidationWhenNotArray() {
        $this->assertFalse($this->rule->validate('aa'));
    }

    public function testFailsValidationWhenExtraLanguage() {
        $this->assertFalse($this->rule->validate(['PL' => 'value', 'XX' => 'value']));
    }
}
