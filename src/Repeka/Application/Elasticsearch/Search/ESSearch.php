<?php
namespace Repeka\Application\Elasticsearch\Search;

use Elastica\Query;
use Elastica\Search;
use Repeka\Application\Elasticsearch\ESClient;
use Repeka\Application\Elasticsearch\Mapping\ResourceConstants;
use Repeka\Domain\Repository\MetadataRepository;

class ESSearch extends Search {

    /** @var MetadataRepository */
    private $metadataRepository;

    /** @var Query */
    private $query;

    private $argValArray = [];
    private $size = 10;
    private $offset = 0;

    public function __construct(ESClient $client, string $indexName, MetadataRepository $metadataRepository) {
        parent::__construct($client);
        $this->metadataRepository = $metadataRepository;
        $this->addIndex($indexName);
        $this->addType(ResourceConstants::ES_DOCUMENT_TYPE);
        $this->query = new Query();
    }

    public function addPair($name, $value): bool {
        $metadata = $this->metadataRepository->findByName($name);
        if ($metadata) {
            $this->argValArray[] = ['match' => ['contents.' . $metadata->getId() . '.value' => $value]];
            return true;
        }
        return false;
    }

    public function createQuery() {
        if (!empty($this->argValArray)) {
            $this->query = new Query(
                [
                    'query' => [
                        "bool" => [
                            "must" => [
                                $this->argValArray,
                            ],
                        ],
                    ],
                ]
            );
        }
        $this->query->setSize($this->size);
        $this->query->setFrom($this->offset);
        $this->setQuery($this->query);
    }

    public function setSize(int $size) {
        $this->size = $size;
    }

    public function setOffset(int $offset) {
        $this->offset = $offset;
    }
}
