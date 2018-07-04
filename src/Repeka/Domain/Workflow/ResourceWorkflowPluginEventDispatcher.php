<?php
namespace Repeka\Domain\Workflow;

use Repeka\Domain\Cqrs\Event\BeforeCommandHandlingEvent;
use Repeka\Domain\Cqrs\Event\CommandErrorEvent;
use Repeka\Domain\Cqrs\Event\CommandEventsListener;
use Repeka\Domain\Cqrs\Event\CommandHandledEvent;
use Repeka\Domain\Cqrs\Event\CqrsCommandEvent;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;

class ResourceWorkflowPluginEventDispatcher extends CommandEventsListener {
    /** @var ResourceWorkflowPlugins */
    private $resourceWorkflowPlugins;

    public function __construct(ResourceWorkflowPlugins $resourceWorkflowPlugins) {
        $this->resourceWorkflowPlugins = $resourceWorkflowPlugins;
    }

    public function onBeforeCommandHandling(BeforeCommandHandlingEvent $event): void {
        $this->getPlugins($event, 'beforeEnterPlace');
    }

    public function onCommandHandled(CommandHandledEvent $event): void {
        $this->getPlugins($event, 'afterEnterPlace');
    }

    public function onCommandError(CommandErrorEvent $event): void {
        $this->getPlugins($event, 'failedEnterPlace');
    }

    private function getPlugins(CqrsCommandEvent $event, string $methodName) {
        /** @var ResourceTransitionCommand $command */
        $command = $event->getCommand();
        $resource = $command->getResource();
        if ($resource->hasWorkflow()) {
            $configs = $this->resourceWorkflowPlugins->getPluginsConfig($command->getTransition()->getToIds(), $resource->getWorkflow());
            foreach ($configs as $pluginConfig) {
                $this->resourceWorkflowPlugins->getPlugin($pluginConfig)->{$methodName}($event, $pluginConfig);
            }
        } else {
            return [];
        }
    }
}
