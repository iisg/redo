<?php
namespace Repeka\Domain\Metadata\MetadataImport\Transform;

use DateTime;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\MetadataDateControl\MetadataDateControlConverterUtil;
use Repeka\Domain\Entity\MetadataDateControl\MetadataDateControlMode;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Metadata\MetadataImport\Config\ImportConfig;
use Repeka\Domain\Metadata\MetadataImport\ImportResult;
use Repeka\Domain\Metadata\MetadataImport\Mapping\Mapping;
use Repeka\Domain\Metadata\MetadataImport\MetadataImporter;
use Repeka\Tests\Traits\StubsTrait;

class MetadataImporterTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var MetadataImporter */
    private $importer;

    /** @before */
    public function init() {
        $transforms = $this->createMock(ImportTransformComposite::class);
        $transforms->method('apply')->willReturnCallback(
            function (array $values, array $transformConfig) {
                return array_fill(0, count($values), $transformConfig['name']);
            }
        );
        $this->importer = new MetadataImporter($transforms);
    }

    private function defaultImport(array $data): ImportResult {
        $metadata = $this->createMetadataMock(1);
        $importConfig = new ImportConfig([new Mapping($metadata, 'a', [], [])], []);
        $importResult = $this->importer->import($data, $importConfig);
        return $importResult;
    }

    public function testSimpleImport() {
        $importResult = $this->defaultImport(['a' => ['AA']]);
        $this->assertEmpty($importResult->getInvalidMetadataKeys());
        $this->assertEmpty($importResult->getUnfitTypeValues());
        $this->assertCount(1, $importResult->getAcceptedValues());
        $this->assertArrayHasKey(1, $importResult->getAcceptedValues());
        $this->assertEquals([['value' => 'AA']], $importResult->getAcceptedValues()[1]);
    }

    public function testImportsEmptyArray() {
        $importResult = $this->defaultImport([]);
        $this->assertEmpty($importResult->getAcceptedValues()->toArray());
    }

    public function testImportsIfDataContainsValuesNotArrays() {
        $importResult = $this->defaultImport(['a' => 'b']);
        $this->assertEquals([['value' => 'b']], $importResult->getAcceptedValues()[1]);
    }

    public function testDoesNotHaveProblemsWithExtraData() {
        $importResult = $this->defaultImport(['c' => 'b']);
        $this->assertEmpty($importResult->getAcceptedValues()->toArray());
    }

    public function testApplyingTransforms() {
        $metadata = $this->createMetadataMock(1, 2);
        $importConfig = new ImportConfig([new Mapping($metadata, 'a', [['name' => 'transformA']], [])], []);
        $importResult = $this->importer->import(['a' => ['AA']], $importConfig);
        $this->assertEquals([['value' => 'transformA']], $importResult->getAcceptedValues()[1]);
    }

    public function testIntegerMetadata() {
        $metadata = $this->createMetadataMock(1, 2, MetadataControl::INTEGER());
        $importConfig = new ImportConfig([new Mapping($metadata, 'a', [], [])], []);
        $importResult = $this->importer->import(['a' => [34]], $importConfig);
        $this->assertEquals([['value' => 34]], $importResult->getAcceptedValues()[1]);
    }

    public function testIntegerMetadataNumericValue() {
        $metadata = $this->createMetadataMock(1, 2, MetadataControl::INTEGER());
        $importConfig = new ImportConfig([new Mapping($metadata, 'a', [], [])], []);
        $importResult = $this->importer->import(['a' => ['34']], $importConfig);
        $this->assertEquals([['value' => 34]], $importResult->getAcceptedValues()[1]);
    }

    public function testIntegerMetadataNonNumericValue() {
        $metadata = $this->createMetadataMock(1, 2, MetadataControl::INTEGER());
        $importConfig = new ImportConfig([new Mapping($metadata, 'a', [], [])], []);
        $importResult = $this->importer->import(['a' => ['abc']], $importConfig);
        $this->assertEmpty($importResult->getAcceptedValues()->toArray());
        $this->assertEquals(['abc'], $importResult->getUnfitTypeValues()[1]);
    }

    public function testBooleanMetadata() {
        $metadata = $this->createMetadataMock(1, 2, MetadataControl::BOOLEAN());
        $importConfig = new ImportConfig([new Mapping($metadata, 'a', [], [])], []);
        $importResult = $this->importer->import(['a' => [true, '1', '0']], $importConfig);
        $this->assertEquals(
            [['value' => true], ['value' => true], ['value' => false]],
            $importResult->getAcceptedValues()[1]
        );
    }

    public function testFlexibleDateMetadata() {
        //Example given string '1888-1999'
        $dateFrom = '1888';
        $dateTo = '1999';
        $metadata = $this->createMetadataMock(1, 2, MetadataControl::FLEXIBLE_DATE());
        $importConfig = new ImportConfig([new Mapping($metadata, 'a', [], [])], []);
        $importResult = $this->importer->import(['a' => [$dateFrom . '-' . $dateTo]], $importConfig);
        $flexibleDate = [
            'from' => DateTime::createFromFormat('Y', $dateFrom)->format(DateTime::ATOM),
            'to' => DateTime::createFromFormat('Y', $dateTo)->format(DateTime::ATOM),
            'mode' => MetadataDateControlMode::RANGE,
            'rangeMode' => MetadataDateControlMode::YEAR,
        ];
        $flexibleDate = MetadataDateControlConverterUtil::convertDateToFlexibleDate(
            $flexibleDate['from'],
            $flexibleDate['to'],
            $flexibleDate['mode'],
            $flexibleDate['rangeMode']
        )->toArray();
        $this->assertEquals(
            ResourceContents::fromArray([1 => [['value' => $flexibleDate]]]),
            $importResult->getAcceptedValues()
        );
    }

    public function testSomeValuesFitSomeUnfit() {
        $metadata = $this->createMetadataMock(1, 2, MetadataControl::INTEGER());
        $importConfig = new ImportConfig([new Mapping($metadata, 'a', [], [])], []);
        $importResult = $this->importer->import(['a' => ['1', '0', false, 'abc', 23]], $importConfig);
        $this->assertEquals(
            [0 => ['value' => 1], 1 => ['value' => 0], 2 => ['value' => 23]],
            $importResult->getAcceptedValues()[1]
        );
        $this->assertEquals([false, 'abc'], $importResult->getUnfitTypeValues()[1]);
    }

    public function testImportWithSubmetadata() {
        $metadata = $this->createMetadataMock(1);
        $metadata2 = $this->createMetadataMock(2);
        $subMetadataMapping = new Mapping($metadata2, '', [['name' => 'subTransform']], []);
        $importConfig = new ImportConfig([new Mapping($metadata, 'a', [], [$subMetadataMapping])], []);
        $importResult = $this->importer->import(['a' => ['AA']], $importConfig);
        $this->assertEmpty($importResult->getInvalidMetadataKeys());
        $this->assertEmpty($importResult->getUnfitTypeValues());
        $this->assertCount(1, $importResult->getAcceptedValues());
        $this->assertArrayHasKey(1, $importResult->getAcceptedValues());
        $this->assertEquals(
            [['value' => 'AA', 'submetadata' => [2 => [['value' => 'subTransform']]]]],
            $importResult->getAcceptedValues()[1]
        );
    }
}
