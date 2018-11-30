<?php
namespace Repeka\Domain\Metadata\MetadataImport\Transform;

class JoinImportTransformTest extends \PHPUnit_Framework_TestCase {
    /** @var JoinImportTransform */
    private $joinTransform;

    protected function setUp() {
        $this->joinTransform = new JoinImportTransform();
    }

    public function testTransforming() {
        $this->assertEquals(['a - b'], $this->joinTransform->apply(['a', 'b'], ['glue' => ' - '], []));
    }

    public function testDefaultGlue() {
        $this->assertEquals(['a, b'], $this->joinTransform->apply(['a', 'b'], [], []));
    }

    public function testTransformingArrayOfArrays() {
        $this->assertEquals([['a, b'], ['1, 2']], $this->joinTransform->apply([['a', 'b'], ['1', '2']], [], []));
    }

    public function testUnjoinableDataReturnSameData() {
        $this->assertEquals([1, ['a'], '3'], $this->joinTransform->apply([1, ['a'], '3'], [], []));
    }
}
