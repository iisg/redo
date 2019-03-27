<?php
namespace Repeka\Application\Elasticsearch\Model;

class ElasticSearchContext {
    /** @var array */
    private $stopWords;
    /** @var string */
    private $indexName;

    public function __construct(string $indexName, array $stopWords) {
        $this->indexName = $indexName;
        $this->stopWords = $stopWords;
    }

    /** @return string */
    public function getIndexName(): string {
        return $this->indexName;
    }

    /** @return array */
    public function getStopWords(): array {
        return $this->stopWords;
    }
}
