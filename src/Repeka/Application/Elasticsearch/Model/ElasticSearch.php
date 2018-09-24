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

    /** @var ESContentsAdjuster */
    private $esContentsAdjuster;

    public function __construct(ESClient $client, ESContentsAdjuster $esContentsAdjuster, string $indexName) {
        $this->client = $client;
        $this->esContentsAdjuster = $esContentsAdjuster;
        $this->index = $this->client->getIndex($indexName);
        $this->type = $this->index->getType(ResourceConstants::ES_DOCUMENT_TYPE);
    }

    private function createDocument(ResourceEntity $resource): Document {
        $data =
            [
                'id' => $resource->getId(),
                'kindId' => $resource->getKind()->getId(),
                'contents' => $this->esContentsAdjuster->adjustContents($resource->getContents()->toArray()),
                'resourceClass' => $resource->getResourceClass(),
            ];
        return new Document($resource->getId(), $data);
    }

    public function insertDocument(ResourceEntity $resource) {
        $document = $this->createDocument($resource);
        $this->type->addDocument($document);
        $this->type->getIndex()->refresh();
    }

    /**
     * @param ResourceEntity[] $resources
     **/
    public function insertDocuments($resources) {
        $documents = [];
        foreach ($resources as $resource) {
            $documents[] = $this->createDocument($resource);
        }
        $this->type->addDocuments($documents);
        $this->type->getIndex()->refresh();
    }
}
