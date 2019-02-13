<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\MetadataValue;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Metadata\MetadataValueAdjuster\MetadataValueAdjusterComposite;
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
    /** @var MetadataValueAdjusterComposite */
    private $metadataValueAdjuster;

    public function __construct(
        ResourceDisplayStrategyEvaluator $evaluator,
        ResourceRepository $resourceRepository,
        MetadataValueAdjusterComposite $metadataValueAdjuster
    ) {
        $this->evaluator = $evaluator;
        $this->resourceRepository = $resourceRepository;
        $this->metadataValueAdjuster = $metadataValueAdjuster;
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
                $contents = $contents->withReplacedValues($metadata, $values);
                $contents = $contents->mapAllValues(
                    function (MetadataValue $value, int $metadataId) use ($metadata) {
                        if ($metadataId == $metadata->getId()) {
                            return $this->metadataValueAdjuster->adjustMetadataValue($value, $metadata->getControl());
                        } else {
                            return $value;
                        }
                    }
                );
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
