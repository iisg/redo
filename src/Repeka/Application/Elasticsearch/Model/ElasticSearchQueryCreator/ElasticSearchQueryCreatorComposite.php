<?php
namespace Repeka\Application\Elasticsearch\Model\ElasticSearchQueryCreator;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;

class ElasticSearchQueryCreatorComposite implements ElasticSearchQueryCreator {
    private $controlCreatorsMap = [];
    /** @var ElasticSearchQueryCreator[] */
    private $creators;
    /** @var DefaultElasticSearchQueryCreator */
    private $defaultElasticSearchQueryCreator;

    /** @param ElasticSearchQueryCreator[] $creators */
    public function __construct(iterable $creators) {
        $this->creators = $creators;
    }

    public function supports(MetadataControl $control): bool {
        return false;
    }

    private function buildCreatorsMap() {
        $this->defaultElasticSearchQueryCreator = new DefaultElasticSearchQueryCreator();
        foreach (MetadataControl::values() as $control) {
            $this->controlCreatorsMap[$control->getValue()] = $this->defaultElasticSearchQueryCreator;
            foreach ($this->creators as $elasticSearchQueryCreator) {
                if ($elasticSearchQueryCreator->supports($control)) {
                    $this->controlCreatorsMap[$control->getValue()] = $elasticSearchQueryCreator;
                    break;
                }
            }
        }
    }

    public function createSearchQuery($filter, Metadata $metadata) {
        if (!$this->controlCreatorsMap) {
            $this->buildCreatorsMap();
        }
        /** @var ElasticSearchQueryCreator $elasticSearchQueryCreator */
        $elasticSearchQueryCreator = $this->controlCreatorsMap[$metadata->getControl()->getValue()];
        return $elasticSearchQueryCreator->createSearchQuery($filter, $metadata);
    }
}
