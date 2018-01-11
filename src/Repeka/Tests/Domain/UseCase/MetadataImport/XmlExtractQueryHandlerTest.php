<?php
namespace Repeka\Tests\Domain\UseCase\MetadataImport;

use PHPUnit_Framework_TestCase;
use Repeka\Domain\MetadataImport\Xml\XmlArrayDataExtractor;
use Repeka\Domain\UseCase\MetadataImport\XmlExtractQuery;
use Repeka\Domain\UseCase\MetadataImport\XmlExtractQueryHandler;

class XmlExtractQueryHandlerTest extends PHPUnit_Framework_TestCase {
    public function testHandling() {
        $query = new XmlExtractQuery('<abc>ala</abc>', ['Imię' => 'abc']);
        $dataExtractor = $this->createMock(XmlArrayDataExtractor::class);
        $dataExtractor->expects($this->once())
            ->method('import')
            ->with($query->getMappings(), $query->getXml())
            ->willReturn([1 => 'ala']);
        $handler = new XmlExtractQueryHandler($dataExtractor);
        $result = $handler->handle($query);
        $this->assertEquals([1 => 'ala'], $result);
    }
}
