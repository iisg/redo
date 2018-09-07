<?php
namespace Repeka\Tests\Domain\UseCase\MetadataImport;

use PHPUnit_Framework_TestCase;
use Repeka\Domain\Metadata\MetadataImport\Config\ImportConfig;
use Repeka\Domain\Metadata\MetadataImport\ImportResult;
use Repeka\Domain\Metadata\MetadataImport\MetadataImporter;
use Repeka\Domain\UseCase\MetadataImport\MetadataImportQuery;
use Repeka\Domain\UseCase\MetadataImport\MetadataImportQueryHandler;

class MetadataImportQueryHandlerTest extends PHPUnit_Framework_TestCase {
    public function testHandling() {
        $importConfig = $this->createMock(ImportConfig::class);
        $query = new MetadataImportQuery(['a' => 1], $importConfig);
        $metadataImporter = $this->createMock(MetadataImporter::class);
        $importResult = $this->createMock(ImportResult::class);
        $metadataImporter->expects($this->once())
            ->method('import')
            ->with($query->getData(), $importConfig)
            ->willReturn($importResult);
        $handler = new MetadataImportQueryHandler($metadataImporter);
        $result = $handler->handle($query);
        $this->assertEquals($importResult, $result);
    }
}
