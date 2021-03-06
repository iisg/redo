<?php
namespace Repeka\Tests\Application\Elasticsearch;

use Elastica\Type;
use Repeka\Application\Elasticsearch\ESIndexManager;
use Repeka\Application\Elasticsearch\Mapping\FtsConstants;

class ESIndexManagerTest extends ElasticsearchTest {
    public function testCreatingIndex() {
        $numberOfShards = 3;
        $numberOfReplicas = 1;
        $typeMock = $this->createMock(Type::class);
        $this->indexMock->expects($this->atLeastOnce())->method('exists')->willReturn(false);
        $this->indexMock->expects($this->once())->method('create')->with(
            [
                'number_of_shards' => $numberOfShards,
                'number_of_replicas' => $numberOfReplicas,
                'index.mapping.ignore_malformed' => true,
                'analysis' => [
                    'filter' => [
                        'pl_stop' => [
                            "type" => "stop",
                            "stopwords" => self::STOP_WORDS,
                        ],
                    ],
                    'analyzer' => [
                        'default' => [
                            "tokenizer" => "standard",
                            "filter" => ["standard", "lowercase", "pl_stop", "morfologik_stem"],
                        ],
                    ],
                ],
            ]
        );
        $this->indexMock->expects($this->once())->method('getType')->with(FtsConstants::ES_DOCUMENT_TYPE)->willReturn($typeMock);
        (new ESIndexManager($this->clientMock, $this->mappingMock, self::INDEX_NAME, self::STOP_WORDS))
            ->create($numberOfShards, $numberOfReplicas);
    }

    public function testCreatingExistingIndex() {
        $this->expectException('Exception');
        $this->indexMock->expects($this->once())->method('exists')->willReturn(true);
        (new ESIndexManager($this->clientMock, $this->mappingMock, self::INDEX_NAME, self::STOP_WORDS))->create(3, 1);
    }

    public function testDeletingIndex() {
        $this->indexMock->expects($this->once())->method('exists')->willReturn(true);
        $this->indexMock->expects($this->once())->method('delete')->with();
        (new ESIndexManager($this->clientMock, $this->mappingMock, self::INDEX_NAME, self::STOP_WORDS))->delete();
    }

    public function testDeletingNonExistentIndex() {
        $this->expectException('Exception');
        $this->indexMock->expects($this->once())->method('exists')->willReturn(false);
        (new ESIndexManager($this->clientMock, $this->mappingMock, self::INDEX_NAME, self::STOP_WORDS))->delete();
    }
}
