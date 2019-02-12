<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\ResourceEntity;
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

    public function __construct(ResourceDisplayStrategyEvaluator $evaluator, ResourceRepository $resourceRepository) {
        $this->evaluator = $evaluator;
        $this->resourceRepository = $resourceRepository;
    }

    /**
     * @return ResourceEntity[]
     */
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
            $value = $this->evaluator->render($resource, $metadata->getDisplayStrategy(), $usedMetadataCollector);
            if (!trim($value) && $metadata->getId() == SystemMetadata::RESOURCE_LABEL) {
                $value = $this->evaluator->render($resource, '#{{r.id}}');
            }
            if ($contents->getValuesWithoutSubmetadata($metadata) != [$value]) {
                $changed = true;
                $contents = $contents->withReplacedValues($metadata, $value);
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
