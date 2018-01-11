<?php
namespace Repeka\Domain\MetadataImport\Mapping;

use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\MetadataImport\Config\ImportConfigFactory;

class ImportConfigFactoryTest extends \PHPUnit_Framework_TestCase {
    /** @var ResourceKind */
    private $resourceKindMock;

    /** @var ImportConfigFactory */
    private $factory;

    /** @before */
    protected function init() {
        $mappingLoader = $this->createMock(MappingLoader::class);
        $mappingLoader->method('load')->willReturn(new MappingLoaderResult([], []));
        $this->factory = new ImportConfigFactory($mappingLoader);
        $this->resourceKindMock = $this->createMock(ResourceKind::class);
        $this->resourceKindMock;
    }

    public function testLoads() {
        $this->assertNotNull($this->factory->fromArray(['mappings' => [[]]], $this->resourceKindMock));
    }

    public function testFailsIfNoMapping() {
        $this->expectException(\InvalidArgumentException::class);
        $this->assertNotNull($this->factory->fromArray(['blabla' => [[]]], $this->resourceKindMock));
    }

    public function testFailsIfEmptyMapping() {
        $this->expectException(\InvalidArgumentException::class);
        $this->assertNotNull($this->factory->fromArray(['mappings' => []], $this->resourceKindMock));
    }

    public function testLoadsFromJsonString() {
        $this->assertNotNull($this->factory->fromString('{"mappings": {"a": {}}}', $this->resourceKindMock));
    }

    public function testFailsOnJsonSyntaxError() {
        $this->expectException(\InvalidArgumentException::class);
        $this->assertNotNull($this->factory->fromString('{"mappings": {"a": {', $this->resourceKindMock));
    }

    public function testLoadsFromUserMappingsFile() {
        $sampleUserDataMappingsConfig = __DIR__ . '/../../../../../../var/config/user_data_mapping.json.sample';
        $this->assertNotNull($this->factory->fromFile($sampleUserDataMappingsConfig, $this->resourceKindMock));
    }
}
