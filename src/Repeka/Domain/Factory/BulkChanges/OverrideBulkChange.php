<?php
namespace Repeka\Domain\Factory\BulkChanges;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Metadata\MetadataValueAdjuster\ResourceContentsAdjuster;
use Repeka\Domain\Service\ResourceDisplayStrategyEvaluator;

class OverrideBulkChange extends BulkChange {
    /** @var string $displayStrategy */
    protected $displayStrategy;
    /** @var int $metadataId */
    protected $metadataId;
    /** @var ResourceDisplayStrategyEvaluator */
    protected $displayStrategyEvaluator;
    /** @var ResourceContentsAdjuster */
    protected $resourceContentsAdjuster;

    public function __construct(
        ResourceDisplayStrategyEvaluator $displayStrategyEvaluator,
        ResourceContentsAdjuster $resourceContentsAdjuster,
        array $changeConfig = []
    ) {
        $this->displayStrategyEvaluator = $displayStrategyEvaluator;
        $this->resourceContentsAdjuster = $resourceContentsAdjuster;
        $this->displayStrategy = $changeConfig['displayStrategy'] ?? '';
        $this->metadataId = $changeConfig['metadataId'] ?? 0;
    }

    public function createForChange(array $changeConfig): BulkChange {
        return new self($this->displayStrategyEvaluator, $this->resourceContentsAdjuster, $changeConfig);
    }

    protected function getChangeConfig(): array {
        return [
            'displayStrategy' => $this->displayStrategy,
            'metadataId' => $this->metadataId,
        ];
    }

    public function apply(ResourceEntity $resource): ResourceEntity {
        if ($resource->getKind()->hasMetadata($this->metadataId)) {
            $values = $this->displayStrategyEvaluator->renderToMetadataValues($resource, $this->displayStrategy);
            $newContents = $resource->getContents()->withReplacedValues($this->metadataId, $values);
            $newContents = $this->resourceContentsAdjuster->adjust($newContents);
            $resource->updateContents($newContents);
        }
        return $resource;
    }
}
