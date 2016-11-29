<?php
namespace Repeka\Tests\Application\Elasticsearch;

use Elastica\Index;
use Repeka\Application\Elasticsearch\ESClient;
use Repeka\Application\Elasticsearch\Mapping\ESMappingFactory;

abstract class ElasticsearchTest extends \PHPUnit_Framework_TestCase {
    const TEST_MAPPING = ['TEST_MAPPING'];
    const INDEX_NAME = 'TEST_INDEX';

    /** @var ESMappingFactory */
    protected $mappingFactoryMock;
    /** @var \PHPUnit_Framework_MockObject_MockObject|Index */
    protected $indexMock;
    /** @var ESClient */
    protected $clientMock;

    protected function setUp() {
        $this->mappingFactoryMock = $this->createMock(ESMappingFactory::class);
        $this->mappingFactoryMock->method('getMappingArray')->willReturn(self::TEST_MAPPING);
        $this->indexMock = $this->createMock(Index::class);
        $this->clientMock = $this->createMock(ESClient::class);
        $this->clientMock->method('getIndex')->with(self::INDEX_NAME)->willReturn($this->indexMock);
    }
}
