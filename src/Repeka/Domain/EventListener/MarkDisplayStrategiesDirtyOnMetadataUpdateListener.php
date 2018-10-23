<?php
namespace Repeka\Domain\EventListener;

use Repeka\Domain\Cqrs\Event\BeforeCommandHandlingEvent;
use Repeka\Domain\Cqrs\Event\CommandEventsListener;
use Repeka\Domain\Cqrs\Event\CommandHandledEvent;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
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
        if ($metadata->getControl() == MetadataControl::DISPLAY_STRATEGY()) {
            $event->setDataForHandledEvent(self::class, $metadata->getConstraints()['displayStrategy']);
        }
    }

    public function onCommandHandled(CommandHandledEvent $event): void {
        $displayStrategyBefore = $event->getDataFromBeforeEvent(self::class);
        if ($displayStrategyBefore) {
            /** @var Metadata $metadata */
            $metadata = $event->getResult();
            $displayStrategyAfter = $metadata->getConstraints()['displayStrategy'];
            if ($displayStrategyBefore != $displayStrategyAfter) {
                $resourceKindsQuery = ResourceKindListQuery::builder()
                    ->filterByMetadataId($metadata->getId())
                    ->build();
                $resourceKinds = $this->resourceKindRepository->findByQuery($resourceKindsQuery);
                $resourceKindsWithoutOverrides = array_filter(
                    $resourceKinds,
                    function (ResourceKind $resourceKind) use ($metadata) {
                        $rkMetadata = $resourceKind->getMetadataById($metadata->getId());
                        $overriddenConstraints = $rkMetadata->getOverrides()['constraints'] ?? [];
                        return !isset($overriddenConstraints['displayStrategy']) || !$overriddenConstraints['displayStrategy'];
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
