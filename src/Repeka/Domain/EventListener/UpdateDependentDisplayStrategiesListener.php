<?php
namespace Repeka\Domain\EventListener;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandBus;
use Repeka\Domain\Cqrs\Event\BeforeCommandHandlingEvent;
use Repeka\Domain\Cqrs\Event\CommandEventsListener;
use Repeka\Domain\Cqrs\Event\CommandHandledEvent;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Resource\ResourceEvaluateDisplayStrategiesCommand;
use Repeka\Domain\UseCase\Resource\ResourceEvaluateDisplayStrategiesFromDependenciesCommand;
use Repeka\Domain\UseCase\Resource\ResourceGodUpdateCommand;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;

class UpdateDependentDisplayStrategiesListener extends CommandEventsListener {
    /** @var ResourceRepository */
    private $resourceRepository;
    /** @var CommandBus */
    private $commandBus;

    /**
     * If we detect recursive display strategies (dependent on each other), how many times should we try them to settle down?
     * @var int
     */
    private const MAX_EVALUATION_DEPTH = 5;

    /**
     * If there are more resources to evaluate, mark them as dirty for postponed evaluation instead of evaluating them immediately.
     * @var int
     */
    private const MARK_AS_DIRTY_QUANTITY_THRESHOLD = 30;

    public function __construct(ResourceRepository $resourceRepository, CommandBus $commandBus) {
        $this->resourceRepository = $resourceRepository;
        $this->commandBus = $commandBus;
    }

    public function onBeforeCommandHandling(BeforeCommandHandlingEvent $event): void {
        /** @var ResourceTransitionCommand $command */
        $command = $event->getCommand();
        $event->setDataForHandledEvent(self::class, $command->getResource()->getContents());
    }

    public function onCommandHandled(CommandHandledEvent $event): void {
        $contentsBefore = $event->getDataFromBeforeEvent(self::class);
        $command = $event->getCommand();
        if ($contentsBefore && count($contentsBefore)) {
            /** @var ResourceEntity $resource */
            $resource = $event->getResult();
            $evaluationDepth = $this->getEvaluationDepth($command);
            if ($evaluationDepth > self::MAX_EVALUATION_DEPTH) {
                return;
            }
            $changedMetadataIds = $this->detectChangedMetadataIds($resource, $contentsBefore);
            if ($changedMetadataIds) {
                $resources = $this->resourceRepository->findByDisplayStrategyDependencies($resource, $changedMetadataIds);
                if (count($resources) > self::MARK_AS_DIRTY_QUANTITY_THRESHOLD) {
                    $this->resourceRepository->markDisplayStrategiesDirty($resources);
                } else {
                    foreach ($resources as $resourceToUpdate) {
                        $dependentMetadataIds = $resourceToUpdate->getDependentMetadataIds($resource, $changedMetadataIds);
                        $evaluateCommand =
                            (new ResourceEvaluateDisplayStrategiesFromDependenciesCommand($resourceToUpdate, $dependentMetadataIds))
                                ->setEvaluationDepth($evaluationDepth + 1);
                        $this->commandBus->handle($evaluateCommand);
                    }
                }
            }
        }
    }

    private function detectChangedMetadataIds(ResourceEntity $resource, ResourceContents $contentsBefore): array {
        $contentsAfter = $resource->getContents();
        $changedMetadataIds = [];
        foreach ($resource->getKind()->getMetadataIds() as $metadataId) {
            $valuesBefore = $contentsBefore->getValues($metadataId);
            $valuesAfter = $contentsAfter->getValues($metadataId);
            if ($valuesBefore != $valuesAfter) {
                $changedMetadataIds[] = $metadataId;
            }
        }
        return $changedMetadataIds;
    }

    private function getEvaluationDepth(Command $command): int {
        return $command instanceof ResourceEvaluateDisplayStrategiesFromDependenciesCommand
            ? $command->getEvaluationDepth()
            : 0;
    }

    protected function subscribedFor(): array {
        return [ResourceTransitionCommand::class, ResourceEvaluateDisplayStrategiesCommand::class, ResourceGodUpdateCommand::class];
    }
}
