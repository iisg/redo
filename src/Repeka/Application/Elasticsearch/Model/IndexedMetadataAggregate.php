<?php
namespace Repeka\Application\Elasticsearch\Model;

interface IndexedMetadataAggregate {
    public function addMetadata(IndexedMetadata $metadata);
}
