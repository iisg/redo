<?php
namespace Repeka\Tests\Application\Elasticsearch;

use Elastica\Index;
use Repeka\Application\Elasticsearch\ESClient;
use Repeka\Application\Elasticsearch\Mapping\ESMapping;

abstract class ElasticsearchTest extends \PHPUnit_Framework_TestCase {
    const TEST_MAPPING = ['TEST_MAPPING'];
    const INDEX_NAME = 'TEST_INDEX';
    const STOP_WORDS = ['i', 'to', 'dla'];

    /** @var ESMapping */
    protected $mappingMock;
    /** @var \PHPUnit_Framework_MockObject_MockObject|Index */
    protected $indexMock;
    /** @var ESClient */
    protected $clientMock;

    protected function setUp() {
        $this->mappingMock = new ESMapping();
        $this->indexMock = $this->createMock(Index::class);
        $this->clientMock = $this->createMock(ESClient::class);
        $this->clientMock->method('getIndex')->with(self::INDEX_NAME)->willReturn($this->indexMock);
    }
}
