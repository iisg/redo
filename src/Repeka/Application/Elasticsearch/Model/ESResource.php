<?php
namespace Repeka\Application\Elasticsearch\Model;

use Elastica\Document;
use Repeka\Application\Elasticsearch\ESClient;
use Repeka\Application\Elasticsearch\Mapping\ResourceConstants;

// Resource is a reserved keyword in PHP 7
class ESResource implements IndexedMetadataAggregate {
    /** @var ESClient */
    private $client;

    /** @var \Elastica\Index */
    private $index;

    /** @var IndexedMetadata[] */
    private $metadataList = [];

    public function __construct(ESClient $client, string $indexName) {
        $this->client = $client;
        $this->index = $this->client->getIndex($indexName);
    }

    public function addMetadata(IndexedMetadata $metadata) {
        $this->metadataList[] = $metadata;
    }

    public function insert() {
        $type = $this->index->getType(ResourceConstants::ES_DOCUMENT_TYPE);
        $document = new Document('', ['metadata' => $this->getMetadataArray()]);
        $type->addDocument($document);
    }

    private function getMetadataArray(): array {
        $result = [];
        foreach ($this->metadataList as $metadata) {
            $result[] = $metadata->toArray();
        }
        return $result;
    }
}
