<?php
namespace Repeka\Domain\MetadataImport\Transform;

class SplitImportTransformTest extends \PHPUnit_Framework_TestCase {
    /** @var SplitImportTransform */
    private $splitTransform;

    protected function setUp() {
        $this->splitTransform = new SplitImportTransform();
    }

    public function testTransforming() {
        $this->assertEquals([['a', 'b']], $this->splitTransform->apply(['a,b'], []));
    }

    public function testTransformingWithSeparator() {
        $this->assertEquals([['a', 'b']], $this->splitTransform->apply(['a-b'], ['separator' => '-']));
    }

    public function testPassingNotString() {
        $this->assertEquals([['a']], $this->splitTransform->apply([['a']], []));
    }
}
