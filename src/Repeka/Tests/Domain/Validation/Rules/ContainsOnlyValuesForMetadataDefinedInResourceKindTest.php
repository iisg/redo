<?php
namespace Repeka\Tests\Domain\Validation\Rules;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Validation\Validator;
use Respect\Validation\Exceptions\NestedValidationException;

class ContainsOnlyValuesForMetadataDefinedInResourceKindTest extends \PHPUnit_Framework_TestCase {
    private $resourceKind;

    protected function setUp() {
        $metadata1 = $this->createMock(Metadata::class);
        $metadata2 = $this->createMock(Metadata::class);
        $metadata1->expects($this->any())->method('getBaseId')->willReturn(1);
        $metadata2->expects($this->any())->method('getBaseId')->willReturn(2);
        $this->resourceKind = $this->createMock(ResourceKind::class);
        $this->resourceKind->expects($this->any())->method('getMetadataList')->willReturn([$metadata1, $metadata2]);
    }

    public function testEmptyArrayPassesValidation() {
        $this->assertTrue(Validator::containsOnlyValuesForMetadataDefinedInResourceKind($this->resourceKind)->validate([]));
    }

    public function testPassingValidationWithOneItem() {
        $this->assertTrue(Validator::containsOnlyValuesForMetadataDefinedInResourceKind($this->resourceKind)->validate([1 => '']));
    }

    public function testPassingValidationWithTwoItems() {
        $this->assertTrue(Validator::containsOnlyValuesForMetadataDefinedInResourceKind($this->resourceKind)->validate([1 => '', 2 => '']));
    }

    public function testFailingValidationIfOneUnknownItem() {
        $this->assertFalse(Validator::containsOnlyValuesForMetadataDefinedInResourceKind($this->resourceKind)
            ->validate([3 => '', 2 => '']));
    }

    public function testFailingValidationIfOnlyUnknownItem() {
        $this->assertFalse(Validator::containsOnlyValuesForMetadataDefinedInResourceKind($this->resourceKind)->validate([3 => '']));
    }

    public function testFailingValidationIfNotAnArray() {
        $this->assertFalse(Validator::containsOnlyValuesForMetadataDefinedInResourceKind($this->resourceKind)->validate(1));
    }

    public function testTellsWhichItemIsUnknown() {
        try {
            Validator::containsOnlyValuesForMetadataDefinedInResourceKind($this->resourceKind)->assert([666 => '']);
        } catch (NestedValidationException $e) {
            $this->assertContains('666', $e->getFullMessage());
        }
    }
}
