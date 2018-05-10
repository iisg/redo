<?php
namespace Repeka\Tests\Application\Elasticsearch\Model;

use Elastica\Document;
use Elastica\Type;
use Repeka\Application\Elasticsearch\Mapping\ResourceConstants;
use Repeka\Application\Elasticsearch\Model\ESResource;
use Repeka\Application\Elasticsearch\Model\IndexedMetadata;
use Repeka\Tests\Application\Elasticsearch\ElasticsearchTest;

class ESResourceTest extends ElasticsearchTest {
    const METADATA1_VALUE = 'one';
    const METADATA2_VALUE = 'two';

    public function testInsertingDocument() {
        $typeMock = $this->createMock(Type::class);
        $typeMock->expects($this->once())->method('addDocument')->with(
            $this->callback(
                function ($doc) {
                    /** @var $doc Document */
                    $this->assertEquals(
                        [
                            'metadata' => [
                                ['value' => self::METADATA1_VALUE],
                                ['value' => self::METADATA2_VALUE],
                            ],
                        ],
                        $doc->getData()
                    );
                    return true;
                }
            )
        );
        $this->indexMock->method('getType')->with(ResourceConstants::ES_DOCUMENT_TYPE)->willReturn($typeMock);
        $res = new ESResource($this->clientMock, self::INDEX_NAME);
        /** @var IndexedMetadata|\PHPUnit_Framework_MockObject_MockObject $mockMetadata1 */
        $mockMetadata1 = $this->createMock('Repeka\Application\Elasticsearch\Model\IndexedMetadata');
        $mockMetadata1->method('toArray')->willReturn(['value' => self::METADATA1_VALUE]);
        /** @var IndexedMetadata|\PHPUnit_Framework_MockObject_MockObject $mockMetadata2 */
        $mockMetadata2 = $this->createMock('Repeka\Application\Elasticsearch\Model\IndexedMetadata');
        $mockMetadata2->method('toArray')->willReturn(['value' => self::METADATA2_VALUE]);
        $res->addMetadata($mockMetadata1);
        $res->addMetadata($mockMetadata2);
        $res->insert();
    }
}
