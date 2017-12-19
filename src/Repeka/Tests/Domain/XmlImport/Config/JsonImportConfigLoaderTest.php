<?php
namespace Repeka\Domain\XmlImport\Config;

use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\XmlImport\Expression\ValueExpression;
use Repeka\Domain\XmlImport\Mapping\Mapping;
use Repeka\Domain\XmlImport\Mapping\MappingLoader;
use Repeka\Domain\XmlImport\Mapping\MappingLoaderResult;
use Repeka\Domain\XmlImport\Transform\TransformLoader;

class JsonImportConfigLoaderTest extends \PHPUnit_Framework_TestCase {
    /** @var TransformLoader|\PHPUnit_Framework_MockObject_MockObject */
    private $transformLoader;
    /** @var MappingLoader|\PHPUnit_Framework_MockObject_MockObject */
    private $mappingLoader;

    /** @var JsonImportConfigLoader */
    private $loader;

    protected function setUp() {
        $this->transformLoader = $this->createMock(TransformLoader::class);
        $this->mappingLoader = $this->createMock(MappingLoader::class);
        $this->loader = new JsonImportConfigLoader($this->transformLoader, $this->mappingLoader);
    }

    private function mappingMock(string $key, array $requiredTransformNames): Mapping {
        $expression = $this->createMock(ValueExpression::class);
        $expression->method('getRequiredTransformNames')->willReturn($requiredTransformNames);
        /** @var Mapping|\PHPUnit_Framework_MockObject_MockObject $mapping */
        $mapping = $this->createMock(Mapping::class);
        $mapping->method('getConfigKey')->willReturn($key);
        $mapping->method('getExpression')->willReturn($expression);
        return $mapping;
    }

    public function testDetectsMissingTransforms() {
        $this->expectException(MissingTransformsException::class);
        $dummyConfig = [
            'transforms' => [],
            'mappings' => [],
        ];
        $this->transformLoader->method('load')->willReturn(['b' => null]);
        $this->mappingLoader->method('load')->willReturn(new MappingLoaderResult([
            $this->mappingMock('ab', ['a', 'b']),
            $this->mappingMock('bc', ['b', 'c']),
        ], []));
        /** @var ResourceKind $dummyResourceKind */
        $dummyResourceKind = $this->createMock(ResourceKind::class);
        $this->loader->load($dummyConfig, $dummyResourceKind);
    }
}
