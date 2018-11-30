<?php
namespace Repeka\Domain\Metadata\MetadataImport\Transform;

class ArrayColumnImportTransformTest extends \PHPUnit_Framework_TestCase {
    /** @var ArrayColumnImportTransform */
    private $arrayColumnTransform;

    protected function setUp() {
        $this->arrayColumnTransform = new ArrayColumnImportTransform();
    }

    public function testTransformingWithOneKey() {
        $this->assertEquals(
            [['abc', 'def']],
            $this->arrayColumnTransform->apply([['a' => ['abc', 'def'], 'b' => []]], ['keys' => 'a'], [])
        );
    }

    public function testTransformingWithMultipleKeys() {
        $this->assertEquals(
            [['abc', 'def', 'x']],
            $this->arrayColumnTransform->apply(
                [['a' => ['abc', 'def'], 'b' => ['x']]],
                ['keys' => 'a,b'],
                []
            )
        );
    }

    public function testTransformingWithMultipleKeysReverted() {
        $this->assertEquals(
            [['x', 'sad', 'asd']],
            $this->arrayColumnTransform->apply(
                [['a' => ['sad', 'asd'], 'b' => ['x']]],
                ['keys' => 'b,a'],
                []
            )
        );
    }

    public function testTransformingWithMultipleElements() {
        $this->assertEquals(
            [['abc', 'cde', 'x'], ['123', '456', 'y']],
            $this->arrayColumnTransform->apply(
                [['a' => ['abc', 'cde'], 'b' => ['x']], ['a' => ['123', '456'], 'b' => ['y']]],
                ['keys' => 'a,b'],
                []
            )
        );
    }
}
