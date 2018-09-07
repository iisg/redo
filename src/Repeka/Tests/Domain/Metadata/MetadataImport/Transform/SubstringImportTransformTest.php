<?php
namespace Repeka\Domain\Metadata\MetadataImport\Transform;

class SubstringImportTransformTest extends \PHPUnit_Framework_TestCase {
    /** @var SubstringImportTransform */
    private $transform;

    protected function setUp() {
        $this->transform = new SubstringImportTransform();
    }

    public function testTransforming() {
        $this->assertEquals(['bcd'], $this->transform->apply(['abcdef'], ['start' => 1, 'length' => 3]));
    }

    public function testWithoutStart() {
        $this->assertEquals(['abc'], $this->transform->apply(['abcdef'], ['length' => 3]));
    }

    public function testWithoutEnd() {
        $this->assertEquals(['def'], $this->transform->apply(['abcdef'], ['start' => 3]));
    }

    public function testWithoutParams() {
        $this->assertEquals(['abcdef'], $this->transform->apply(['abcdef'], []));
    }

    public function testUtf8() {
        $this->assertEquals(['ó'], $this->transform->apply(['żółw'], ['start' => 1, 'length' => 1]));
    }
}
