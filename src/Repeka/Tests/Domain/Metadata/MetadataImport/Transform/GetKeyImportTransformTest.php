<?php
namespace Repeka\Domain\Metadata\MetadataImport\Transform;

class GetKeyImportTransformTest extends \PHPUnit_Framework_TestCase {
    /** @var GetKeyImportTransform */
    private $getKeyTransform;

    public function testImport() {
        $transformResult = $this->getKeyTransform->apply([['animal' => 'unicorn']], ['key' => 'animal'], [], null);
        $this->assertEquals(['unicorn'], $transformResult);
    }

    public function testThrowsExceptionWithNoKey() {
        $this->expectException(\InvalidArgumentException::class);
        $this->getKeyTransform->apply([['animal' => 'unicorn']], [], [], null);
    }

    public function testTransformsToNullIfKeyNotFound() {
        $transformResult = $this->getKeyTransform->apply([['animal' => 'unicorn']], ['key' => 'dog'], [], null);
        $this->assertEquals([null], $transformResult);
    }

    public function testThrowsExceptionWhenNotArrayOfArrays() {
        $this->expectException(\InvalidArgumentException::class);
        $this->getKeyTransform->apply(['animal' => 'unicorn'], ['key' => 'animal'], [], null);
    }

    protected function setUp() {
        $this->getKeyTransform = new GetKeyImportTransform();
    }
}
