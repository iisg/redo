<?php
namespace Repeka\Domain\Metadata\MetadataImport\Transform;

class DefaultValueImportTransformTest extends \PHPUnit_Framework_TestCase {
    /** @var DefaultValueImportTransform */
    private $transform;

    protected function setUp() {
        $this->transform = new DefaultValueImportTransform();
    }

    public function testAddsDefaultValueIfEmpty() {
        $this->assertEquals(['defaulty'], $this->transform->apply([], ['value' => 'defaulty'], []));
    }

    public function testIgnoresIfValueExists() {
        $this->assertEquals(['existing'], $this->transform->apply(['existing'], ['value' => 'defaulty'], []));
    }

    public function testOverridesExistingValue() {
        $this->assertEquals(['defaulty'], $this->transform->apply(['existing'], ['value' => 'defaulty', 'override' => true], []));
    }

    public function testAddsMultipleValues() {
        $this->assertEquals(['1', '2', '3'], $this->transform->apply([], ['value' => ['1', '2', '3']], []));
    }

    public function testOverridesMultipleValues() {
        $this->assertEquals(['1', '2', '3'], $this->transform->apply(['ex0', 'ex1'], ['value' => ['1', '2', '3'], 'override' => true], []));
    }

    public function testEmptySettingIsInvalid() {
        $this->expectException(\InvalidArgumentException::class);
        $this->assertEquals([], $this->transform->apply(['ex0', 'ex1'], [], []));
    }

    public function testOverrideIsFalseByDefault() {
        $this->assertEquals(
            $this->transform->apply(['existing'], ['value' => 'default0'], []),
            $this->transform->apply(['existing'], ['value' => 'default1', 'override' => false], [])
        );
    }
}
