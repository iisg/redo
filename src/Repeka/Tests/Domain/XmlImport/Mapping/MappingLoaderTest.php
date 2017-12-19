<?php
namespace Repeka\Domain\XmlImport\Mapping;

use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\XmlImport\Expression\Compiler\ExpressionCompiler;
use Repeka\Domain\XmlImport\Expression\Compiler\ExpressionCompilerException;
use Repeka\Domain\XmlImport\Expression\ConcatenationExpression;
use Repeka\Tests\Traits\StubsTrait;

class MappingLoaderTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ExpressionCompiler|\PHPUnit_Framework_MockObject_MockObject */
    private $expressionCompiler;
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
        $this->expressionCompiler = $this->createMock(ExpressionCompiler::class);
        $this->expressionCompiler->method('compile')->willReturn($this->createMock(ConcatenationExpression::class));
        $this->loader = new MappingLoader($this->expressionCompiler);
    }

    private function defaultInput(): array {
        return [
            '1' => ['selector' => '[test]', 'value' => 'a'],
            'test' => ['selector' => '[test2]', 'value' => 'b'],
            'invalidMetadataName' => ['selector' => '[test3]', 'value' => 'c'],
        ];
    }

    public function testLoadsMappings() {
        $this->expressionCompiler->expects($this->exactly(3))->method('compile');
        $result = $this->loader->load($this->defaultInput(), $this->resourceKind);
        $this->assertCount(2, $result->getLoadedMappings());
        $this->assertInstanceOf(Mapping::class, $result->getLoadedMappings()[0]);
        $this->assertInstanceOf(Mapping::class, $result->getLoadedMappings()[1]);
        $this->assertEquals(['invalidMetadataName'], $result->getKeysMissingFromResourceKind());
    }

    public function testWrapsExpressionCompilerExceptions() {
        $this->expectException(ExpressionCompilerException::class);
        $exception = new ExpressionCompilerException("test");
        $this->expressionCompiler->expects($this->once())->method('compile')->willThrowException($exception);
        $this->loader->load($this->defaultInput(), $this->resourceKind);
    }

    public function testRejectsWithExtraParams() {
        $this->expectException(InvalidMappingException::class);
        $this->loader->load([
            'invalidMetadataName' => ['selector' => '[test3]', 'value' => 'c'],
            'test' => ['selector' => '[test2]', 'value' => 'b', 'extra?' => 'extra!'],
        ], $this->resourceKind);
    }

    public function testRejectsWithMissingParams() {
        $this->expectException(InvalidMappingException::class);
        $this->loader->load([
            'invalidMetadataName' => ['selector' => '[test3]', 'value' => 'c'],
            'test' => ['selector' => '[test2]'],
        ], $this->resourceKind);
    }

    public function testRejectsWithInvalidSelector() {
        $this->expectException(InvalidSelectorException::class);
        $this->loader->load([
            'invalidMetadataName' => ['selector' => '[test3]', 'value' => 'c'],
            'test' => ['selector' => 'datafield[ind1', 'value' => 'd'],
        ], $this->resourceKind);
    }
}
