<?php
namespace Repeka\Domain\Metadata\MetadataImport\Transform;

class GetKeyImportTransformTest extends \PHPUnit_Framework_TestCase {
    /** @var GetKeyImportTransform */
    private $getKeyTransform;

    public function testImport() {
        $transformResult = $this->getKeyTransform->apply([['animal' => 'unicorn']], ['key' => 'animal'], []);
        $this->assertEquals(['unicorn'], $transformResult);
    }

    public function testThrowsExceptionWithNoKey() {
        $this->expectException(\InvalidArgumentException::class);
        $this->getKeyTransform->apply([['animal' => 'unicorn']], [], []);
    }

    public function testTransformsToNullIfKeyNotFound() {
        $transformResult = $this->getKeyTransform->apply([['animal' => 'unicorn']], ['key' => 'dog'], []);
        $this->assertEquals([null], $transformResult);
    }

    public function testThrowsExceptionWhenNotArrayOfArrays() {
        $this->expectException(\InvalidArgumentException::class);
        $this->getKeyTransform->apply(['animal' => 'unicorn'], ['key' => 'animal'], []);
    }

    protected function setUp() {
        $this->getKeyTransform = new GetKeyImportTransform();
    }
}
