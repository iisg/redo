<?php
namespace Repeka\Domain\MetadataImport\Mapping;

use Repeka\Domain\Entity\ResourceKind;
use Repeka\Tests\Traits\StubsTrait;
use Respect\Validation\Exceptions\ValidationException;

class MappingLoaderTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceKind|\PHPUnit_Framework_MockObject_MockObject */
    private $resourceKind;

    /** @var MappingLoader */
    private $loader;

    protected function setUp() {
        $metadata = $this->createMetadataMock(1);
        $metadata->method('getName')->willReturn('test');
        $this->resourceKind = $this->createMock(ResourceKind::class);
        $this->resourceKind->method('getMetadataById')->willReturnCallback(function (string $id) use ($metadata) {
            if ($id === '1') {
                return $metadata;
            }
            throw new \InvalidArgumentException();
        });
        $this->resourceKind->method('getMetadataByName')->willReturnCallback(function (string $name) use ($metadata) {
            if ($name === 'test') {
                return $metadata;
            }
            throw new \InvalidArgumentException();
        });
        $this->loader = new MappingLoader();
    }

    private function defaultInput(): array {
        return [
            '1' => ['key' => 'a'],
            'test' => ['key' => 'b', 'transforms' => [['name' => 'transformA']]],
            'invalidMetadataName' => ['key' => 'c'],
        ];
    }

    public function testLoadsMappings() {
        $result = $this->loader->load($this->defaultInput(), $this->resourceKind);
        $this->assertCount(2, $result->getLoadedMappings());
        $this->assertInstanceOf(Mapping::class, $result->getLoadedMappings()[0]);
        $this->assertInstanceOf(Mapping::class, $result->getLoadedMappings()[1]);
        $this->assertEquals(['invalidMetadataName'], $result->getKeysMissingFromResourceKind());
    }

    public function testCreatingGoodMappings() {
        $result = $this->loader->load($this->defaultInput(), $this->resourceKind);
        $mapping1 = $result->getLoadedMappings()[0];
        $this->assertEquals('a', $mapping1->getImportKey());
        $this->assertEmpty($mapping1->getTransformsConfig());
        $this->assertEquals($this->resourceKind->getMetadataById(1), $mapping1->getMetadata());
        $mapping2 = $result->getLoadedMappings()[1];
        $this->assertEquals('b', $mapping2->getImportKey());
        $this->assertEquals([['name' => 'transformA']], $mapping2->getTransformsConfig());
        $this->assertEquals($this->resourceKind->getMetadataById(1), $mapping1->getMetadata());
    }

    public function testRejectsWithExtraParams() {
        $this->expectException(ValidationException::class);
        $this->loader->load([
            'invalidMetadataName' => ['selector' => '[test3]', 'value' => 'c'],
            'test' => ['selector' => '[test2]', 'value' => 'b', 'extra?' => 'extra!'],
        ], $this->resourceKind);
    }

    public function testRejectsWithMissingParams() {
        $this->expectException(ValidationException::class);
        $this->loader->load([
            'invalidMetadataName' => ['selector' => '[test3]', 'value' => 'c'],
            'test' => ['selector' => '[test2]'],
        ], $this->resourceKind);
    }
}
