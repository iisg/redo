<?php
namespace Repeka\Tests\Domain\UseCase\MetadataImport;

use PHPUnit_Framework_TestCase;
use Repeka\Domain\Metadata\MetadataImport\Xml\MarcxmlArrayDataExtractor;
use Repeka\Domain\UseCase\MetadataImport\MarcxmlExtractQuery;
use Repeka\Domain\UseCase\MetadataImport\MarcxmlExtractQueryHandler;

class MarcxmlExtractQueryHandlerTest extends PHPUnit_Framework_TestCase {
    public function testHandling() {
        $query = new MarcxmlExtractQuery('<abc>ala</abc>');
        $dataExtractor = $this->createMock(MarcxmlArrayDataExtractor::class);
        $handler = new MarcxmlExtractQueryHandler($dataExtractor);
        $dataExtractor->expects($this->once())
            ->method('import')
            ->with($query->getXml())
            ->willReturn([1 => 'ala']);
        $result = $handler->handle($query);
        $this->assertEquals([1 => 'ala'], $result);
    }
}
