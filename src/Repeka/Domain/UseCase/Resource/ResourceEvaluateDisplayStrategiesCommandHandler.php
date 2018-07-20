<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Service\ResourceDisplayStrategyEvaluator;

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
        $displayStrategyMetadata = $resource->getKind()->getMetadataByControl(MetadataControl::DISPLAY_STRATEGY());
        $contents = $resource->getContents();
        $changed = false;
        foreach ($displayStrategyMetadata as $metadata) {
            $value = $this->evaluator->render($resource, $metadata->getConstraints()['displayStrategy']);
            if ($contents->getValues($metadata) != [$value]) {
                $changed = true;
                $contents = $contents->withReplacedValues($metadata, $value);
            }
        }
        if ($changed) {
            $resource->updateContents($contents);
            $resource = $this->resourceRepository->save($resource);
        }
        return $resource;
    }
}
