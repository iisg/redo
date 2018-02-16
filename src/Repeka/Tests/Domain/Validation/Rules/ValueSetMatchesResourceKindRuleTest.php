<?php
namespace Repeka\Tests\Domain\Validation\Rules;

use Assert\InvalidArgumentException;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Validation\Rules\ValueSetMatchesResourceKindRule;
use Repeka\Tests\Traits\StubsTrait;
use Respect\Validation\Exceptions\ValidationException;

class ValueSetMatchesResourceKindRuleTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    private $resourceKind;

    /** @var ValueSetMatchesResourceKindRule */
    private $rule;

    protected function setUp() {
        $metadata1 = $this->createMetadataMock(1);
        $metadata2 = $this->createMetadataMock(2);
        $this->resourceKind = $this->createResourceKindMock(1, 'books', [$metadata1, $metadata2]);
        $this->rule = new ValueSetMatchesResourceKindRule();
    }

    public function testEmptyArrayPassesValidation() {
        $this->assertTrue(
            $this->rule->forResourceKind($this->resourceKind)->validate(ResourceContents::empty())
        );
    }

    public function testPassingValidationWithOneItem() {
        $this->assertTrue(
            $this->rule->forResourceKind($this->resourceKind)->validate(ResourceContents::fromArray([1 => '']))
        );
    }

    public function testPassingValidationWithTwoItems() {
        $this->assertTrue(
            $this->rule->forResourceKind($this->resourceKind)->validate(ResourceContents::fromArray([1 => '', 2 => '']))
        );
    }

    public function testFailingValidationIfOneUnknownItem() {
        $this->assertFalse(
            $this->rule->forResourceKind($this->resourceKind)->validate(ResourceContents::fromArray([3 => '', 2 => '']))
        );
    }

    public function testFailingValidationIfOnlyUnknownItem() {
        $this->assertFalse(
            $this->rule->forResourceKind($this->resourceKind)->validate(ResourceContents::fromArray([3 => '']))
        );
    }

    public function testFailingValidationIfArray() {
        $this->assertFalse($this->rule->forResourceKind($this->resourceKind)->validate([3 => 1]));
    }

    public function testFailingValidationIfNotAResourceContents() {
        $this->assertFalse(
            $this->rule->forResourceKind($this->resourceKind)->validate(1)
        );
    }

    public function testTellsWhichItemIsUnknown() {
        try {
            $this->rule->forResourceKind($this->resourceKind)->assert(ResourceContents::fromArray([666 => '']));
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
        $this->assertTrue($rule->validate(ResourceContents::fromArray([1 => '', 2 => ''])));
        $this->assertTrue($rule->validate(ResourceContents::fromArray([1 => '', 2 => ''])));
    }
}
