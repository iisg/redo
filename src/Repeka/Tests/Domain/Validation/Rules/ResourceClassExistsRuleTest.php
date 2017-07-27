<?php
namespace Repeka\Tests\Domain\Validation\Rules;

use Repeka\Domain\Validation\Rules\ResourceClassExistsRule;

class ResourceClassExistsRuleTest extends \PHPUnit_Framework_TestCase {
    /** @var ResourceClassExistsRule */
    private $validator;

    protected function setUp() {
        $this->validator = new ResourceClassExistsRule(['books', 'dictionaries']);
    }

    public function testRejectsNonExistingResourceClasses() {
        $this->assertFalse($this->validator->validate('resourceClass'));
    }

    public function testAcceptsExistingResourceClass() {
        $this->assertTrue($this->validator->validate('books'));
    }

    public function testRejectsEmptyResourceClass() {
        $this->assertFalse($this->validator->validate(''));
    }

    public function testRejectsWhenResourceClassIsNull() {
        $this->assertFalse($this->validator->validate(null));
    }
}
