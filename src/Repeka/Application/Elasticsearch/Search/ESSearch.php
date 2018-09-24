<?php
namespace Repeka\Application\Elasticsearch\Search;

use Elastica\Query;
use Elastica\Search;
use Repeka\Application\Elasticsearch\ESClient;
use Repeka\Application\Elasticsearch\Mapping\ResourceConstants;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Exception\EntityNotFoundException;
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

    public function addPair($key, $value): bool {
        $metadata = $this->findMetadataForKey($key);
        if ($metadata) {
            $property = $metadata->getId() . '.value_' . $metadata->getControl();
            try {
                $parentMetadata = $metadata->getParent();
                while ($parentMetadata) {
                    $property = $parentMetadata->getId() . '.submetadata.' . $property;
                    $parentMetadata = $parentMetadata->getParent();
                }
            } catch (\InvalidArgumentException $e) {
                $this->argValArray[] = ['match' => ['contents.' . $property => $value]];
                return true;
            }
        }
        return false;
    }

    private function findMetadataForKey(string $key): ?Metadata {
        return $this->findMetadataById($key) ?: $this->findMetadataByName($key);
    }

    private function findMetadataById(string $id): ?Metadata {
        if (!is_numeric($id)) {
            return null;
        }
        try {
            return $this->metadataRepository->findOne(intval($id));
        } catch (EntityNotFoundException $e) {
            return null;
        }
    }

    private function findMetadataByName(string $name): ?Metadata {
        try {
            return $this->metadataRepository->findByName($name);
        } catch (EntityNotFoundException $e) {
            return null;
        }
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
