<?php
namespace Repeka\Application\Elasticsearch;

use Elastica\Type\Mapping;
use Repeka\Application\Elasticsearch\Mapping\ESMappingFactory;
use Repeka\Application\Elasticsearch\Mapping\ResourceConstants;

class ESIndexManager {
    /** @var ESMappingFactory */
    private $mappingFactory;

    /** @var string */
    private $indexName;
    /** @var \Elastica\Index */
    private $index;

    public function __construct(ESClient $client, ESMappingFactory $mappingFactory, string $indexName) {
        $this->indexName = $indexName;
        $this->mappingFactory = $mappingFactory;
        $this->index = $client->getIndex($this->indexName);
    }

    public function create(int $numberOfShards = 1, int $numberOfReplicas = 0) {
        if ($this->index->exists()) {
            throw new \Exception("Index '{$this->indexName}' already exists");
        }
        $this->index->create([
            'number_of_shards' => $numberOfShards,
            'number_of_replicas' => $numberOfReplicas,
        ]);
        $mappingArray = $this->mappingFactory->getMappingArray();
        $type = $this->index->getType(ResourceConstants::ES_DOCUMENT_TYPE);
        (new Mapping($type, $mappingArray))->send();
    }

    public function delete() {
        if (!$this->index->exists()) {
            throw new \Exception("Index '{$this->indexName}' doesn't exist");
        }
        $this->index->delete();
    }

    public function exists(): bool {
        return $this->index->exists();
    }
}
