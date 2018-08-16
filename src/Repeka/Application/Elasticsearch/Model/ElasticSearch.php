<?php
namespace Repeka\Application\Elasticsearch\Model;

use Elastica\Document;
use Elastica\Index;
use Repeka\Application\Elasticsearch\ESClient;
use Repeka\Application\Elasticsearch\Mapping\ResourceConstants;
use Elastica\Type;
use Repeka\Domain\Entity\ResourceEntity;

// Resource is a reserved keyword in PHP 7
class ElasticSearch {
    /** @var ESClient */
    private $client;

    /** @var Index */
    private $index;

    /** @var Type */
    private $type;

    public function __construct(ESClient $client, string $indexName) {
        $this->client = $client;
        $this->index = $this->client->getIndex($indexName);
        $this->type = $this->index->getType(ResourceConstants::ES_DOCUMENT_TYPE);
    }

    public function insertDocument(ResourceEntity $resource) {
        $document = new Document($resource->getId(), $resource->getFtsData());
        $this->type->addDocument($document);
        $this->type->getIndex()->refresh();
    }

    /**
     * @param ResourceEntity[] $resources
     **/
    public function insertDocuments($resources) {
        $documents = [];
        foreach ($resources as $resource) {
            $documents[] = new Document($resource->getId(), $resource->getFtsData());
        }
        $this->type->addDocuments($documents);
        $this->type->getIndex()->refresh();
    }
}
