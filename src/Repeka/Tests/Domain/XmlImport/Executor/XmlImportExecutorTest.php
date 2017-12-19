<?php
namespace Repeka\Domain\XmlImport\Executor;

use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\XmlImport\Config\XmlImportConfig;
use Repeka\Tests\Traits\StubsTrait;

class XmlImportExecutorTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var XmlImportConfig|\PHPUnit_Framework_MockObject_MockObject */
    private $dummyConfig;
    /** @var RawValueXmlImporter|\PHPUnit_Framework_MockObject_MockObject */
    private $rawValueImporter;

    /** @var XmlImportExecutor */
    private $executor;

    protected function setUp() {
        $this->dummyConfig = new XmlImportConfig([], [], []);
        $this->rawValueImporter = $this->createMock(RawValueXmlImporter::class);
        $this->executor = new XmlImportExecutor($this->rawValueImporter);
    }

    public function testImportsIntegerValues() {
        $rk = $this->createResourceKindMock([
            $this->createMetadataMock(10, 1, MetadataControl::INTEGER()),
        ]);
        $this->rawValueImporter->method('import')->willReturn([
            1 => ['', '0', '1', '123456', 'test'],
        ]);
        $result = $this->executor->execute('', $this->dummyConfig, $rk);
        $this->assertEquals([1 => [0, 1, 123456]], $result->getAcceptedValues());
        $this->assertEquals([1 => ['', 'test']], $result->getUnfitTypeValues());
    }

    public function testImportsBooleanValues() {
        $rk = $this->createResourceKindMock([
            $this->createMetadataMock(10, 1, MetadataControl::BOOLEAN()),
        ]);
        $this->rawValueImporter->method('import')->willReturn([
            1 => ['', '0', '1', 'true', 'false', 'test'],
        ]);
        $result = $this->executor->execute('', $this->dummyConfig, $rk);
        $this->assertEquals([1 => [false, false, true, true, false]], $result->getAcceptedValues());
        $this->assertEquals([1 => ['test']], $result->getUnfitTypeValues());
    }

    public function testImportsMixedValues() {
        $rk = $this->createResourceKindMock([
            $this->createMetadataMock(11, 1, MetadataControl::TEXT()),
            $this->createMetadataMock(12, 2, MetadataControl::INTEGER()),
            $this->createMetadataMock(13, 3, MetadataControl::BOOLEAN()),
            $this->createMetadataMock(14, 4, MetadataControl::TEXTAREA()),
        ]);
        $this->rawValueImporter->method('import')->willReturn([
            1 => ['a', '', '0', 'true'],
            2 => ['0'],
            3 => ['true', 'false'],
            4 => ['b', '', '1', 'false'],
        ]);
        $result = $this->executor->execute('', $this->dummyConfig, $rk);
        $this->assertEquals([
            1 => ['a', '', '0', 'true'],
            2 => [0],
            3 => [true, false],
            4 => ['b', '', '1', 'false'],
        ], $result->getAcceptedValues());
        $this->assertEquals([], $result->getUnfitTypeValues());
    }

    public function testCopiesInvalidMetadataKeys() {
        $rk = $this->createResourceKindMock();
        $this->rawValueImporter->method('import')->willReturn([]);
        $config = new XmlImportConfig([], [], ['test']);
        $result = $this->executor->execute('', $config, $rk);
        $this->assertEquals($config->getInvalidMetadataKeys(), $result->getInvalidMetadataKeys());
    }
}
