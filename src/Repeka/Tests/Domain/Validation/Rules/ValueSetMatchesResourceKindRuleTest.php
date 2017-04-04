<?php
namespace Repeka\Tests\Domain\Validation\Rules;

use Assert\InvalidArgumentException;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Validation\Rules\ValueSetMatchesResourceKindRule;
use Respect\Validation\Exceptions\ValidationException;

class ValueSetMatchesResourceKindRuleTest extends \PHPUnit_Framework_TestCase {
    private $resourceKind;

    /** @var ValueSetMatchesResourceKindRule */
    private $rule;

    protected function setUp() {
        $metadata1 = $this->createMock(Metadata::class);
        $metadata2 = $this->createMock(Metadata::class);
        $metadata1->expects($this->any())->method('getBaseId')->willReturn(1);
        $metadata2->expects($this->any())->method('getBaseId')->willReturn(2);
        $this->resourceKind = $this->createMock(ResourceKind::class);
        $this->resourceKind->expects($this->any())->method('getMetadataList')->willReturn([$metadata1, $metadata2]);
        $this->rule = new ValueSetMatchesResourceKindRule();
    }

    public function testEmptyArrayPassesValidation() {
        $this->assertTrue(
            $this->rule->forResourceKind($this->resourceKind)->validate([])
        );
    }

    public function testPassingValidationWithOneItem() {
        $this->assertTrue(
            $this->rule->forResourceKind($this->resourceKind)->validate([1 => ''])
        );
    }

    public function testPassingValidationWithTwoItems() {
        $this->assertTrue(
            $this->rule->forResourceKind($this->resourceKind)->validate([1 => '', 2 => ''])
        );
    }

    public function testFailingValidationIfOneUnknownItem() {
        $this->assertFalse(
            $this->rule->forResourceKind($this->resourceKind)->validate([3 => '', 2 => ''])
        );
    }

    public function testFailingValidationIfOnlyUnknownItem() {
        $this->assertFalse(
            $this->rule->forResourceKind($this->resourceKind)->validate([3 => ''])
        );
    }

    public function testFailingValidationIfNotAnArray() {
        $this->assertFalse(
            $this->rule->forResourceKind($this->resourceKind)->validate(1)
        );
    }

    public function testTellsWhichItemIsUnknown() {
        try {
            $this->rule->forResourceKind($this->resourceKind)->assert([666 => '']);
        } catch (ValidationException $e) {
            $this->assertContains('666', $e->getMessage());
        }
    }

    public function testFailisWhenResourceKindIsNotSet() {
        $this->expectException(InvalidArgumentException::class);
        $this->rule->validate([1 => '', 2 => '']);
    }

    public function testDoesNotRememberPreviouslySetResourceKind() {
        $this->expectException(InvalidArgumentException::class);
        $this->rule->forResourceKind($this->resourceKind)->validate([1 => '', 2 => '']);
        $this->rule->validate([1 => '', 2 => '']);
    }

    public function testCanUseConfiguredInstanceMoreThanOnce() {
        $rule = $this->rule->forResourceKind($this->resourceKind);
        $this->assertTrue($rule->validate([1 => '', 2 => '']));
        $this->assertTrue($rule->validate([1 => '', 2 => '']));
    }
}
