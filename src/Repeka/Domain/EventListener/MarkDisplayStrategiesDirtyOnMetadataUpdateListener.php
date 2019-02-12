<?php
namespace Repeka\Domain\EventListener;

use Repeka\Domain\Cqrs\Event\BeforeCommandHandlingEvent;
use Repeka\Domain\Cqrs\Event\CommandEventsListener;
use Repeka\Domain\Cqrs\Event\CommandHandledEvent;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateCommand;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindListQuery;

class MarkDisplayStrategiesDirtyOnMetadataUpdateListener extends CommandEventsListener {
    /** @var ResourceRepository */
    private $resourceRepository;
    /** @var ResourceKindRepository */
    private $resourceKindRepository;

    public function __construct(ResourceRepository $resourceRepository, ResourceKindRepository $resourceKindRepository) {
        $this->resourceRepository = $resourceRepository;
        $this->resourceKindRepository = $resourceKindRepository;
    }

    public function onBeforeCommandHandling(BeforeCommandHandlingEvent $event): void {
        /** @var MetadataUpdateCommand $command */
        $command = $event->getCommand();
        $metadata = $command->getMetadata();
        if ($metadata->isDynamic()) {
            $event->setDataForHandledEvent(self::class, $metadata->getDisplayStrategy());
        }
    }

    public function onCommandHandled(CommandHandledEvent $event): void {
        $displayStrategyBefore = $event->getDataFromBeforeEvent(self::class);
        if ($displayStrategyBefore) {
            /** @var Metadata $metadata */
            $metadata = $event->getResult();
            $displayStrategyAfter = $metadata->getDisplayStrategy();
            if ($displayStrategyBefore != $displayStrategyAfter) {
                $resourceKindsQuery = ResourceKindListQuery::builder()
                    ->filterByMetadataId($metadata->getId())
                    ->build();
                $resourceKinds = $this->resourceKindRepository->findByQuery($resourceKindsQuery);
                $resourceKindsWithoutOverrides = array_filter(
                    $resourceKinds,
                    function (ResourceKind $resourceKind) use ($metadata) {
                        $rkMetadata = $resourceKind->getMetadataById($metadata->getId());
                        $overriddenDisplayStrategy = $rkMetadata->getOverrides()['displayStrategy'] ?? null;
                        return !$overriddenDisplayStrategy;
                    }
                );
                $this->resourceRepository->markDisplayStrategiesDirty($resourceKindsWithoutOverrides);
            }
        }
    }

    protected function subscribedFor(): array {
        return [MetadataUpdateCommand::class];
    }
}
