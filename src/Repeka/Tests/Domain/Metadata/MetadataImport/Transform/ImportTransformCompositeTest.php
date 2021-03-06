<?php
namespace Repeka\Domain\Metadata\MetadataImport\Transform;

class ImportTransformCompositeTest extends \PHPUnit_Framework_TestCase {
    /** @var ImportTransform */
    private $transformComposite;

    protected function setUp() {
        $transformA = $this->createMock(ImportTransform::class);
        $transformB = $this->createMock(ImportTransform::class);
        $transformA->method('getName')->willReturn('transformA');
        $transformB->method('getName')->willReturn('transformB');
        $transformA->method('apply')->willReturn(['transformedByA']);
        $transformB->method('apply')->willReturn(['transformedByB']);
        $this->transformComposite = new ImportTransformComposite([$transformA, $transformB]);
    }

    public function testTransforming() {
        $transformed = $this->transformComposite->apply(['a'], ['name' => 'transformA'], [], null);
        $this->assertEquals(['transformedByA'], $transformed);
    }

    public function testThrowsOnInvalidTransform() {
        $this->expectException(\InvalidArgumentException::class);
        $this->transformComposite->apply(['a'], ['name' => 'transformC'], [], null);
    }
}
