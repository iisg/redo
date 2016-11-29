<?php
namespace Repeka\Application\Elasticsearch;

use Elastica\Client;

class ESClient extends Client {
    public function __construct(string $host, int $port, string $proxy) {
        parent::__construct([
            'host' => $host,
            'port' => $port,
            'proxy' => $proxy
        ]);
    }
}
