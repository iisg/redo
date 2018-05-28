<?php
namespace Repeka\Tests\Domain\Validation\Rules;

use Repeka\Domain\Validation\Rules\NoAssigneeMetadataInFirstPlaceRule;
use Repeka\Tests\Traits\StubsTrait;

class NoAssigneeMetadataInFirstPlaceRuleTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var  NoAssigneeMetadataInFirstPlaceRule */
    private $rule;

    protected function setUp() {

        $this->rule = new NoAssigneeMetadataInFirstPlaceRule();
    }

    public function testFailsWhenAssigneeMetadataInFirstPlace() {
        $place = $this->createWorkflowPlaceMock(1, [], [2]);
        $place->expects($this->once())->method('toArray')->willReturn(['assigneeMetadataIds' => [2]]);
        $this->assertFalse($this->rule->validate([$place]));
    }

    public function testPassWhenAssigneeMetadataInFirstPlace() {
        $place = $this->createWorkflowPlaceMock(1);
        $place->expects($this->once())->method('toArray')->willReturn(['assigneeMetadataIds' => []]);
        $this->assertTrue($this->rule->validate([$place]));
    }
}
