<?php
namespace Repeka\Tests\Domain\Validation\MetadataConstraints;

use Repeka\Application\Service\PhpRegexNormalizer;
use Repeka\Domain\Validation\MetadataConstraints\RegexConstraint;

class RegexConstraintTest extends \PHPUnit_Framework_TestCase {
    /** @var RegexConstraint */
    private $constraint;

    protected function setUp() {
        $this->constraint = new RegexConstraint(new PhpRegexNormalizer());
    }

    public function testRejectsBadArguments() {
        $this->assertFalse($this->constraint->isConfigValid(['bad']));
        $this->assertFalse($this->constraint->isConfigValid(0));
        $this->assertFalse($this->constraint->isConfigValid(null));
    }

    public function testAcceptsGoodArguments() {
        $this->assertTrue($this->constraint->isConfigValid(''));
        $this->assertTrue($this->constraint->isConfigValid('ok'));
    }

    public function testValidatesUsualValues() {
        $this->assertTrue($this->constraint->isValueValid('^a', ['aXb']));
        $this->assertTrue($this->constraint->isValueValid('b$', ['aXb']));
        $this->assertTrue($this->constraint->isValueValid('', ['whatever']));
        $this->assertTrue($this->constraint->isValueValid('', ['']));
        $this->assertTrue($this->constraint->isValueValid('', ['', 'test']));
        $this->assertTrue($this->constraint->isValueValid('^$', ['']));
        $this->assertTrue($this->constraint->isValueValid('^abc$', ['abc']));
        $this->assertTrue($this->constraint->isValueValid('^abc$', ['abc', 'abc']));
        $this->assertTrue($this->constraint->isValueValid('^a.*z$', ['az', 'aXz', 'a.....z']));
        $this->assertTrue($this->constraint->isValueValid('d.f', ['abcdefgh']));
    }

    public function testValidatesWithRegexesThatNeedEscaping() {
        $this->assertTrue($this->constraint->isValueValid('a\\/b', ['a\\/b']));
        $this->assertFalse($this->constraint->isValueValid('a\\/b', ['a\\\\/b']));
        $this->assertFalse($this->constraint->isValueValid('a\\/b', ['a\\//b']));
    }

    public function testRejectsUsualValues() {
        $this->assertFalse($this->constraint->isValueValid('b$', ['aXB']));
        $this->assertFalse($this->constraint->isValueValid('^abc$', ['xabcx']));
        $this->assertFalse($this->constraint->isValueValid('^abc$', ['abc', 'xabcx']));
    }
}
