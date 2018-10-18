<?php
namespace Repeka\Domain\EventListener;

use Repeka\Domain\Cqrs\CommandBus;
use Repeka\Domain\Cqrs\Event\BeforeCommandHandlingEvent;
use Repeka\Domain\Cqrs\Event\CommandEventsListener;
use Repeka\Domain\Cqrs\Event\CommandHandledEvent;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Resource\ResourceEvaluateDisplayStrategiesCommand;
use Repeka\Domain\UseCase\Resource\ResourceEvaluateDisplayStrategiesFromDependenciesCommand;
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
            $evaluationDepth = 0;
            if ($command instanceof ResourceEvaluateDisplayStrategiesFromDependenciesCommand) {
                $evaluationDepth = $command->getEvaluationDepth();
            }
            if ($evaluationDepth > self::MAX_EVALUATION_DEPTH) {
                return;
            }
            $changedMetadataIds = $this->detectChangedMetadataIds($resource, $contentsBefore);
            if ($changedMetadataIds) {
                $resources = $this->resourceRepository->findByDisplayStrategyDependencies($resource, $changedMetadataIds);
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

    protected function subscribedFor(): array {
        return [ResourceTransitionCommand::class, ResourceEvaluateDisplayStrategiesCommand::class];
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
}
