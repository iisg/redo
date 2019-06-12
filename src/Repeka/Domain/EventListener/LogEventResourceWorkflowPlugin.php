<?php
namespace Repeka\Domain\EventListener;

use Repeka\Domain\Constants\SystemTransition;
use Repeka\Domain\Cqrs\CommandBus;
use Repeka\Domain\Cqrs\Event\CommandHandledEvent;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlacePluginConfiguration;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;
use Repeka\Domain\UseCase\Stats\EventLogCreateCommand;
use Repeka\Domain\Workflow\ResourceWorkflowPlugin;
use Repeka\Domain\Workflow\ResourceWorkflowPluginConfigurationOption;

class LogEventResourceWorkflowPlugin extends ResourceWorkflowPlugin {
    /** @var CommandBus */
    private $commandBus;

    public function __construct(CommandBus $commandBus) {
        $this->commandBus = $commandBus;
    }

    public function afterEnterPlace(CommandHandledEvent $event, ResourceWorkflowPlacePluginConfiguration $config): void {
        /** @var ResourceTransitionCommand $command */
        $command = $event->getCommand();
        $resource = $command->getResource();
        $eventName = $config->getConfigValue('eventName');
        $logOnEdit = $config->getConfigValue('logOnEdit');
        if ($eventName && ($logOnEdit || $command->getTransition()->getId() != SystemTransition::UPDATE)) {
            $event = new EventLogCreateCommand($eventName, $config->getConfigValue('eventGroup'), $resource);
            $this->commandBus->handle($event);
        }
    }

    /** @return ResourceWorkflowPluginConfigurationOption[] */
    public function getConfigurationOptions(): array {
        return [
            new ResourceWorkflowPluginConfigurationOption('eventName', MetadataControl::TEXT()),
            new ResourceWorkflowPluginConfigurationOption('eventGroup', MetadataControl::TEXT()),
            new ResourceWorkflowPluginConfigurationOption('logOnEdit', MetadataControl::BOOLEAN()),
        ];
    }
}
