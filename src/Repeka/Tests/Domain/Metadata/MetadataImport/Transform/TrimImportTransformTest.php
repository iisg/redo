<?php
namespace Repeka\Domain\Metadata\MetadataImport\Transform;

class TrimImportTransformTest extends \PHPUnit_Framework_TestCase {
    /** @var TrimImportTransform */
    private $trimTransform;

    /** @before */
    protected function createInstance() {
        $this->trimTransform = new TrimImportTransform();
    }

    public function testTransforming() {
        $this->assertEquals(['a'], $this->trimTransform->apply([' a '], [], []));
    }

    public function testTransformingMany() {
        $this->assertEquals(['a', 'bś'], $this->trimTransform->apply([' a ', '    bś     '], [], []));
    }

    public function testTransformingWithCharList() {
        $this->assertEquals(['IONIQ'], $this->trimTransform->apply([',, IONIQ! ! '], ['characters' => ' ,!'], []));
    }

    public function testTransformingDeepArray() {
        $this->assertEquals(['b', ['a', 'c']], $this->trimTransform->apply(['b ,,', ['a !!', '  ,  c']], ['characters' => ' ,!'], []));
    }
}
