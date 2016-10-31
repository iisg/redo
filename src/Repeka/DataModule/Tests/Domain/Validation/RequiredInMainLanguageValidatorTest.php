<?php
namespace Repeka\DataModule\Tests\Domain\Validation;

use Repeka\DataModule\Domain\Validation\RequiredInMainLanguageValidator;

class RequiredInMainLanguageValidatorTest extends \PHPUnit_Framework_TestCase {
    /** @var RequiredInMainLanguageValidator */
    private $validator;

    protected function setUp() {
        $this->validator = new RequiredInMainLanguageValidator('PL');
    }

    public function testValidates() {
        $this->assertTrue($this->validator->isValid(['PL' => 'Polish']));
    }

    public function testFailsWhenBlank() {
        $this->assertFalse($this->validator->isValid(['PL' => '       ']));
    }

    public function testFailsWhenNull() {
        $this->assertFalse($this->validator->isValid(['PL' => null]));
    }

    public function testFailsWhenNoMainLanguage() {
        $this->assertFalse($this->validator->isValid(['EN' => 'English']));
    }

    public function testFailsWhenNoValue() {
        $this->assertFalse($this->validator->isValid(null));
    }
}
