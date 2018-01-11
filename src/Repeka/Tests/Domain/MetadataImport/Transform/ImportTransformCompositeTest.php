<?php
namespace Repeka\Domain\MetadataImport\Transform;

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
        $this->transformComposite = new ImportTransformComposite();
        $this->transformComposite->register($transformA);
        $this->transformComposite->register($transformB);
    }

    public function testTranforming() {
        $transformed = $this->transformComposite->apply(['a'], ['name' => 'transformA']);
        $this->assertEquals(['transformedByA'], $transformed);
    }

    public function testThrowsOnInvalidTransform() {
        $this->expectException(\InvalidArgumentException::class);
        $this->transformComposite->apply(['a'], ['name' => 'transformC']);
    }
}
