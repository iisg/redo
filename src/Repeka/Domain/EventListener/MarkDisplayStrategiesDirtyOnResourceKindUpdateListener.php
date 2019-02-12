<?php
namespace Repeka\Domain\EventListener;

use Repeka\Domain\Cqrs\Event\BeforeCommandHandlingEvent;
use Repeka\Domain\Cqrs\Event\CommandEventsListener;
use Repeka\Domain\Cqrs\Event\CommandHandledEvent;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindUpdateCommand;

class MarkDisplayStrategiesDirtyOnResourceKindUpdateListener extends CommandEventsListener {
    /** @var ResourceRepository */
    private $resourceRepository;

    public function __construct(ResourceRepository $resourceRepository) {
        $this->resourceRepository = $resourceRepository;
    }

    public function onBeforeCommandHandling(BeforeCommandHandlingEvent $event): void {
        /** @var ResourceKindUpdateCommand $command */
        $command = $event->getCommand();
        $event->setDataForHandledEvent(self::class, $this->getDisplayStrategiesMap($command->getResourceKind()));
    }

    private function getDisplayStrategiesMap(ResourceKind $resourceKind) {
        $map = [];
        foreach ($resourceKind->getDynamicMetadata() as $dynamicMetadata) {
            $map[$dynamicMetadata->getId()] = $dynamicMetadata->getDisplayStrategy();
        }
        return $map;
    }

    public function onCommandHandled(CommandHandledEvent $event): void {
        $displayStrategiesBefore = $event->getDataFromBeforeEvent(self::class);
        $displayStrategiesAfter = $this->getDisplayStrategiesMap($event->getResult());
        if ($displayStrategiesBefore != $displayStrategiesAfter) {
            $this->resourceRepository->markDisplayStrategiesDirty($event->getCommand()->getResourceKind());
        }
    }

    protected function subscribedFor(): array {
        return [ResourceKindUpdateCommand::class];
    }
}
