<?php
namespace Repeka\Tests\Domain\Validation\Rules;

use Repeka\Domain\Validation\Rules\ContainsUniqueValuesRule;

class ContainsUniqueValuesRuleTest extends \PHPUnit_Framework_TestCase {
    /** @var ContainsUniqueValuesRule */
    private $validator;

    protected function setUp() {
        $this->validator = new ContainsUniqueValuesRule();
    }

    public function testRejectsNonArrays() {
        $this->assertFalse($this->validator->validate(false));
        $this->assertFalse($this->validator->validate(true));
        $this->assertFalse($this->validator->validate(123));
        $this->assertFalse($this->validator->validate('test'));
    }

    public function testAcceptsEmptyArray() {
        $this->assertTrue($this->validator->validate([]));
    }

    public function testAcceptsUniqueArray() {
        $this->assertTrue($this->validator->validate([1, 2, 3]));
    }

    public function testRejectsArrayWithDuplicate() {
        $this->assertFalse($this->validator->validate([1, 2, 2, 3]));
    }
}
