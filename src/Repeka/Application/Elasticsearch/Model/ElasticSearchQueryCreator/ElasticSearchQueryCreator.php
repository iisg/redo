<?php
namespace Repeka\Application\Elasticsearch\Model\ElasticSearchQueryCreator;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;

interface ElasticSearchQueryCreator {
    public function supports(MetadataControl $control): bool;

    public function createSearchQuery($filter, Metadata $metadata);
}
