<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Metadata\MetadataValueAdjuster\ResourceContentsAdjuster;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Service\ResourceDisplayStrategyEvaluator;
use Repeka\Domain\Service\ResourceDisplayStrategyUsedMetadataCollector;
use Repeka\Domain\Utils\EntityUtils;

/**
 * @SuppressWarnings("PHPMD.CyclomaticComplexity")
 * @SuppressWarnings("PHPMD.NPathComplexity")
 */
class ResourceEvaluateDisplayStrategiesCommandHandler {
    /** @var ResourceDisplayStrategyEvaluator */
    private $evaluator;
    /** @var ResourceRepository */
    private $resourceRepository;
    /** @var ResourceContentsAdjuster */
    private $resourceContentsAdjuster;

    public function __construct(
        ResourceDisplayStrategyEvaluator $evaluator,
        ResourceRepository $resourceRepository,
        ResourceContentsAdjuster $resourceContentsAdjuster
    ) {
        $this->evaluator = $evaluator;
        $this->resourceRepository = $resourceRepository;
        $this->resourceContentsAdjuster = $resourceContentsAdjuster;
    }

    public function handle(ResourceEvaluateDisplayStrategiesCommand $command): ResourceEntity {
        $resource = $command->getResource();
        $displayStrategyMetadata = $resource->getKind()->getDynamicMetadata();
        if ($command->getMetadataIds()) {
            $displayStrategyMetadata = EntityUtils::filterByIds($command->getMetadataIds(), $displayStrategyMetadata);
        }
        $contents = $resource->getContents();
        $changed = false;
        foreach ($displayStrategyMetadata as $metadata) {
            $usedMetadataCollector = new ResourceDisplayStrategyUsedMetadataCollector();
            $values = $this->evaluator->renderToMetadataValues($resource, $metadata->getDisplayStrategy(), $usedMetadataCollector);
            if (!$values && $metadata->getId() == SystemMetadata::RESOURCE_LABEL) {
                $values = $this->evaluator->renderToMetadataValues($resource, '#{{r.id}}');
            }
            if ($contents->getValues($metadata) != $values) {
                $changed = true;
                $contents = $this->resourceContentsAdjuster->adjust($contents->withReplacedValues($metadata, $values));
            }
            if ($changed || $resource->isDisplayStrategiesDirty()) {
                $resource->updateDisplayStrategyDependencies($metadata->getId(), $usedMetadataCollector);
            }
        }
        if ($resource->isDisplayStrategiesDirty() && !$command->getMetadataIds()) {
            $changed = true;
            $resource->clearDisplayStrategiesDirty();
        }
        if ($changed) {
            $resource->updateContents($contents);
            $resource = $this->resourceRepository->save($resource);
        }
        return $resource;
    }
}
