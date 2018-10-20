<?php
namespace Repeka\Domain\EventListener;

use Repeka\Domain\Constants\SystemTransition;
use Repeka\Domain\Cqrs\Event\CommandEventsListener;
use Repeka\Domain\Cqrs\Event\CommandHandledEvent;
use Repeka\Domain\Repository\ResourceFtsProvider;
use Repeka\Domain\UseCase\Resource\ResourceDeleteCommand;
use Repeka\Domain\UseCase\Resource\ResourceEvaluateDisplayStrategiesCommand;
use Repeka\Domain\UseCase\Resource\ResourceGodUpdateCommand;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;

class ResourceUpdateFtsIndexListener extends CommandEventsListener {
    /** @var ResourceFtsProvider */
    private $resourceFtsProvider;

    public function __construct(ResourceFtsProvider $resourceFtsProvider) {
        $this->resourceFtsProvider = $resourceFtsProvider;
    }

    public function onCommandHandled(CommandHandledEvent $event): void {
        $command = $event->getCommand();
        try {
            if ($this->isProductiveCommand($command)) {
                /** @var  $ResourceEntity */
                $resource = $event->getResult();
                $this->resourceFtsProvider->index($resource);
            } elseif ($command instanceof ResourceDeleteCommand) {
                $resourceId = $event->getResult();
                $this->resourceFtsProvider->delete($resourceId);
            }
        } catch (\Exception $e) {
            // we want the application to work without FTS
        }
    }

    private function isProductiveCommand($command): bool {
        return ($command instanceof ResourceTransitionCommand && $command->getTransition()->getLabel() !== [SystemTransition::DELETE])
            || $command instanceof ResourceEvaluateDisplayStrategiesCommand
            || $command instanceof ResourceGodUpdateCommand;
    }

    protected function subscribedFor(): array {
        return [
            ResourceTransitionCommand::class,
            ResourceDeleteCommand::class,
            ResourceEvaluateDisplayStrategiesCommand::class,
            ResourceGodUpdateCommand::class,
        ];
    }
}
