<?php
namespace Repeka\Domain\MetadataImport\Transform;

use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Factory\ResourceContentsNormalizer;
use Repeka\Domain\MetadataImport\Config\ImportConfig;
use Repeka\Domain\MetadataImport\ImportResult;
use Repeka\Domain\MetadataImport\Mapping\Mapping;
use Repeka\Domain\MetadataImport\MetadataImporter;
use Repeka\Tests\Traits\StubsTrait;

class MetadataImporterTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var MetadataImporter */
    private $importer;

    /** @before */
    public function init() {
        $transforms = $this->createMock(ImportTransformComposite::class);
        $transforms->method('apply')->willReturnArgument(1);
        $normalizer = $this->createMock(ResourceContentsNormalizer::class);
        $normalizer->method('normalize')->willReturnArgument(0);
        $this->importer = new MetadataImporter($transforms, $normalizer);
    }

    private function defaultImport(array $data): ImportResult {
        $metadata = $this->createMetadataMock(1);
        $importConfig = new ImportConfig([new Mapping($metadata, 'a', [])], []);
        $importResult = $this->importer->import($data, $importConfig);
        return $importResult;
    }

    public function testSimpleImport() {
        $importResult = $this->defaultImport(['a' => ['AA']]);
        $this->assertEmpty($importResult->getInvalidMetadataKeys());
        $this->assertEmpty($importResult->getUnfitTypeValues());
        $this->assertCount(1, $importResult->getAcceptedValues());
        $this->assertArrayHasKey(1, $importResult->getAcceptedValues());
        $this->assertEquals(['AA'], $importResult->getAcceptedValues()[1]);
    }

    public function testImportsEmptyArray() {
        $importResult = $this->defaultImport([]);
        $this->assertEmpty($importResult->getAcceptedValues());
    }

    public function testImportsIfDataContainsValuesNotArrays() {
        $importResult = $this->defaultImport(['a' => 'b']);
        $this->assertEquals(['b'], $importResult->getAcceptedValues()[1]);
    }

    public function testDoesNotHaveProblemsWithExtraData() {
        $importResult = $this->defaultImport(['c' => 'b']);
        $this->assertEmpty($importResult->getAcceptedValues());
    }

    public function testApplyingTransforms() {
        $metadata = $this->createMetadataMock(1, 2);
        $importConfig = new ImportConfig([new Mapping($metadata, 'a', [['name' => 'transformA']])], []);
        $importResult = $this->importer->import(['a' => ['AA']], $importConfig);
        $this->assertEquals(['name' => 'transformA'], $importResult->getAcceptedValues()[1]);
    }

    public function testIntegerMetadata() {
        $metadata = $this->createMetadataMock(1, 2, MetadataControl::INTEGER());
        $importConfig = new ImportConfig([new Mapping($metadata, 'a', [])], []);
        $importResult = $this->importer->import(['a' => [34]], $importConfig);
        $this->assertEquals([34], $importResult->getAcceptedValues()[1]);
    }

    public function testIntegerMetadataNumericValue() {
        $metadata = $this->createMetadataMock(1, 2, MetadataControl::INTEGER());
        $importConfig = new ImportConfig([new Mapping($metadata, 'a', [])], []);
        $importResult = $this->importer->import(['a' => ['34']], $importConfig);
        $this->assertEquals([34], $importResult->getAcceptedValues()[1]);
    }

    public function testIntegerMetadataNonNumericValue() {
        $metadata = $this->createMetadataMock(1, 2, MetadataControl::INTEGER());
        $importConfig = new ImportConfig([new Mapping($metadata, 'a', [])], []);
        $importResult = $this->importer->import(['a' => ['abc']], $importConfig);
        $this->assertEmpty($importResult->getAcceptedValues());
        $this->assertEquals(['abc'], $importResult->getUnfitTypeValues()[1]);
    }

    public function testBooleanMetadata() {
        $metadata = $this->createMetadataMock(1, 2, MetadataControl::BOOLEAN());
        $importConfig = new ImportConfig([new Mapping($metadata, 'a', [])], []);
        $importResult = $this->importer->import(['a' => [true, '1', '0']], $importConfig);
        $this->assertEquals([true, true, false], $importResult->getAcceptedValues()[1]);
    }

    public function testSomeValuesFitSomeUnfit() {
        $metadata = $this->createMetadataMock(1, 2, MetadataControl::INTEGER());
        $importConfig = new ImportConfig([new Mapping($metadata, 'a', [])], []);
        $importResult = $this->importer->import(['a' => ['1', '0', false, 'abc', 23]], $importConfig);
        $this->assertEquals([1, 0, 23], $importResult->getAcceptedValues()[1]);
        $this->assertEquals([false, 'abc'], $importResult->getUnfitTypeValues()[1]);
    }
}
