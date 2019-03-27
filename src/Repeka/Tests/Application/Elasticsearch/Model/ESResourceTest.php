<?php
namespace Repeka\Tests\Application\Elasticsearch\Model;

use Elastica\Document;
use Elastica\Type;
use Repeka\Application\Elasticsearch\Mapping\FtsConstants;
use Repeka\Application\Elasticsearch\Model\ElasticSearch;
use Repeka\Application\Elasticsearch\Model\ElasticSearchContext;
use Repeka\Application\Elasticsearch\Model\ElasticSearchQueryCreator\ElasticSearchQueryCreatorComposite;
use Repeka\Application\Elasticsearch\Model\ESContentsAdjuster;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Service\ResourceFileStorage;
use Repeka\Tests\Application\Elasticsearch\ElasticsearchTest;
use Repeka\Tests\Traits\StubsTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ESResourceTest extends ElasticsearchTest {
    use StubsTrait;

    public function setUp() {
        parent::setUp();
    }

    public function testInsertingDocument() {
        $resourceKind = $this->createResourceKindMock(2);
        $resource = $this->createResourceMock(1, $resourceKind, ['3' => ['value' => 'aaa']]);
        $typeMock = $this->createMock(Type::class);
        $elasticSearchContext = $this->createMock(ElasticSearchContext::class);
        $typeMock->expects($this->once())->method('getIndex')->willReturn($this->indexMock);
        $elasticSearchContext->expects($this->once())->method('getIndexName')->willReturn(ElasticSearchTest::INDEX_NAME);
        $this->indexMock->expects($this->once())->method('refresh');
        $typeMock->expects($this->once())->method('addDocument')->with(
            $this->callback(
                function ($doc) {
                    /** @var $doc Document */
                    $this->assertEquals(
                        [
                            FtsConstants::ID => 1,
                            FtsConstants::KIND_ID => 2,
                            FtsConstants::CONTENTS => ['3' => [['value_text' => 'aaa']]],
                            FtsConstants::RESOURCE_CLASS => 'books',
                        ],
                        $doc->getData()
                    );
                    return true;
                }
            )
        );
        $metadataRepository = $this->createMetadataRepositoryStub(
            [
                $this->createMetadataMock(3, null, MetadataControl::TEXT(), [], 'books', [], 'TEXT'),
            ]
        );
        $elasticSearchQueryCreatorComposite = $this->createMock(ElasticSearchQueryCreatorComposite::class);
        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willReturn($this->createMock(ResourceFileStorage::class));
        $esContentsAdjuster = new ESContentsAdjuster($metadataRepository, $container);
        $this->indexMock->method('getType')->with(FtsConstants::ES_DOCUMENT_TYPE)->willReturn($typeMock);
        $res = new ElasticSearch(
            $this->clientMock,
            $esContentsAdjuster,
            $elasticSearchContext,
            $elasticSearchQueryCreatorComposite
        );
        $res->insertDocument($resource);
    }
}
