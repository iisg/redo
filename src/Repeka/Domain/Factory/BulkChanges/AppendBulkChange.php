<?php
namespace Repeka\Domain\Factory\BulkChanges;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Metadata\MetadataValueAdjuster\ResourceContentsAdjuster;
use Repeka\Domain\Service\ResourceDisplayStrategyEvaluator;

class AppendBulkChange extends OverrideBulkChange {
    /** @var bool $addValuesAtBeginning */
    private $addValuesAtBeginning;

    public function __construct(
        ResourceDisplayStrategyEvaluator $displayStrategyEvaluator,
        ResourceContentsAdjuster $resourceContentsAdjuster,
        array $changeConfig = []
    ) {
        parent::__construct($displayStrategyEvaluator, $resourceContentsAdjuster, $changeConfig);
        $this->addValuesAtBeginning = $changeConfig['addValuesAtBeginning'] ?? false;
    }

    public function createForChange(array $changeConfig): BulkChange {
        return new self($this->displayStrategyEvaluator, $this->resourceContentsAdjuster, $changeConfig);
    }

    protected function getChangeConfig(): array {
        return [
            'displayStrategy' => $this->displayStrategy,
            'metadataId' => $this->metadataId,
            'addValuesAtBeginning' => $this->addValuesAtBeginning,
        ];
    }

    public function apply(ResourceEntity $resource): ResourceEntity {
        if ($resource->getKind()->hasMetadata($this->metadataId)) {
            $values = $this->displayStrategyEvaluator->renderToMetadataValues($resource, $this->displayStrategy);
            $contents = $resource->getContents();
            $oldValues = $contents->getValuesWithoutSubmetadata($this->metadataId);
            $values = array_diff($values, $oldValues);
            if (!empty($values)) {
                $newContents = $contents->withMergedValues($this->metadataId, $values, $this->addValuesAtBeginning);
                $newContents = $this->resourceContentsAdjuster->adjust($newContents);
                $resource->updateContents($newContents);
            }
        }
        return $resource;
    }
}
