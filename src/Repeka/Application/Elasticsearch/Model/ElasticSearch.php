<?php
namespace Repeka\Application\Elasticsearch\Model;

use Assert\Assertion;
use Elastica\Document;
use Elastica\Index;
use Elastica\ResultSet;
use Elastica\Search;
use Elastica\Type;
use Repeka\Application\Elasticsearch\ESClient;
use Repeka\Application\Elasticsearch\Mapping\FtsConstants;
use Repeka\Application\Elasticsearch\Model\ElasticSearchQueryCreator\ElasticSearchQueryCreatorComposite;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\ResourceFtsProvider;
use Repeka\Domain\UseCase\Resource\ResourceListFtsQuery;
use Symfony\Component\Console\Helper\ProgressBar;

/** @SuppressWarnings(PHPMD.CouplingBetweenObjects) */
class ElasticSearch implements ResourceFtsProvider {
    private const MAX_BULK_SIZE_IN_BYTES = 15728640; // 15 MB

    /** @var ESClient */
    private $client;

    /** @var Index */
    private $index;

    /** @var Type */
    private $type;

    /** @var ESContentsAdjuster */
    private $esContentsAdjuster;

    /** @var ElasticSearchQueryCreatorComposite */
    private $elasticSearchQueryCreatorComposite;

    /** @var ElasticSearchContext */
    private $elasticSearchContext;

    public function __construct(
        ESClient $client,
        ESContentsAdjuster $esContentsAdjuster,
        ElasticSearchContext $elasticSearchContext,
        ElasticSearchQueryCreatorComposite $elasticSearchQueryCreatorComposite
    ) {
        $this->client = $client;
        $this->esContentsAdjuster = $esContentsAdjuster;
        $this->index = $this->client->getIndex($elasticSearchContext->getIndexName());
        $this->type = $this->index->getType(FtsConstants::ES_DOCUMENT_TYPE);
        $this->elasticSearchQueryCreatorComposite = $elasticSearchQueryCreatorComposite;
        $this->elasticSearchContext = $elasticSearchContext;
    }

    private function createDocument(ResourceEntity $resource): Document {
        $data = [
            'id' => $resource->getId(),
            'kindId' => $resource->getKind()->getId(),
            'contents' => $this->esContentsAdjuster->adjustContents($resource, $resource->getContents()->toArray()),
            'resourceClass' => $resource->getResourceClass(),
        ];
        return new Document($resource->getId(), $data);
    }

    public function insertDocument(ResourceEntity $resource) {
        $document = $this->createDocument($resource);
        $this->type->addDocument($document);
        $this->type->getIndex()->refresh();
    }

    public function deleteDocument(int $resourceId) {
        $this->type->deleteById(strval($resourceId));
        $this->type->getIndex()->refresh();
    }

    /** @param ResourceEntity[] $resources */
    public function insertDocuments(iterable $resources, ProgressBar $progressBar = null) {
        $documents = [];
        $bulkSize = 0;
        foreach ($resources as $resource) {
            $document = $this->createDocument($resource);
            $bulkSize += mb_strlen(serialize((array)$document->getData()), '8bit');
            $documents[] = $document;
            if ($bulkSize > self::MAX_BULK_SIZE_IN_BYTES) {
                $this->bulkInsertDocumentsToIndex($documents);
                $documents = [];
                $bulkSize = 0;
            }
            if ($progressBar) {
                $progressBar->advance();
            }
        }
        if ($documents) {
            $this->bulkInsertDocumentsToIndex($documents);
        }
        $this->type->getIndex()->refresh();
    }

    private function bulkInsertDocumentsToIndex(array $documents) {
        $response = $this->type->addDocuments($documents);
        Assertion::count($response->getBulkResponses(), count($documents), 'Bulk request failed - nothing has been indexed from this part');
    }

    /** @return ResultSet */
    public function search(ResourceListFtsQuery $query) {
        $esQuery = new ElasticSearchQuery($query, $this->elasticSearchQueryCreatorComposite, $this->elasticSearchContext);
        $search = new Search($this->client);
        $search->addIndex($this->index->getName());
        $search->addType(FtsConstants::ES_DOCUMENT_TYPE);
        $search->setQuery($esQuery->getQuery());
        return $search->search();
    }

    public function index(ResourceEntity $resource): void {
        $this->insertDocument($resource);
    }

    public function delete(int $resourceId): void {
        $this->deleteDocument($resourceId);
    }
}
