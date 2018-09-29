<?php
namespace Repeka\Tests\Domain\Validation\Rules;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Validation\Rules\MetadataGroupExistsRule;
use Repeka\Tests\Traits\StubsTrait;

class MetadataGroupExistsRuleTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var MetadataGroupExistsRule */
    private $rule;

    protected function setUp() {
        $this->rule = new MetadataGroupExistsRule([['id'=>'group0'], ['id'=>'group1']]);
    }

    public function testAcceptsWhenGroupExists() {
        $this->assertTrue($this->rule->validate('group0'));
    }

    public function testAcceptsDefaultGroup() {
        $this->assertTrue($this->rule->validate(Metadata::DEFAULT_GROUP));
    }

    public function testRejectsWhenGroupIsEmpty() {
        $this->assertFalse($this->rule->validate(''));
    }

    public function testRejectsWhenGroupIsNull() {
        $this->assertFalse($this->rule->validate(null));
    }

    public function testRejectsWhenGroupDoesNotExist() {
        $this->assertFalse($this->rule->validate('nonExistingGroup'));
    }
}
