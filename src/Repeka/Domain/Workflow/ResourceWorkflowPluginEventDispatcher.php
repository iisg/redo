<?php
namespace Repeka\Domain\Workflow;

use Repeka\Domain\Cqrs\Event\BeforeCommandHandlingEvent;
use Repeka\Domain\Cqrs\Event\CommandErrorEvent;
use Repeka\Domain\Cqrs\Event\CommandEventsListener;
use Repeka\Domain\Cqrs\Event\CommandHandledEvent;
use Repeka\Domain\Cqrs\Event\CqrsCommandEvent;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;

class ResourceWorkflowPluginEventDispatcher extends CommandEventsListener {
    public static $dispatchPluginEvents = true;

    /** @var ResourceWorkflowPlugins */
    private $resourceWorkflowPlugins;

    public function __construct(ResourceWorkflowPlugins $resourceWorkflowPlugins) {
        $this->resourceWorkflowPlugins = $resourceWorkflowPlugins;
    }

    public function onBeforeCommandHandling(BeforeCommandHandlingEvent $event): void {
        $this->executePlugins($event, 'beforeEnterPlace');
    }

    public function onCommandHandled(CommandHandledEvent $event): void {
        $this->executePlugins($event, 'afterEnterPlace');
    }

    public function onCommandError(CommandErrorEvent $event): void {
        $this->executePlugins($event, 'failedEnterPlace');
    }

    private function executePlugins(CqrsCommandEvent $event, string $methodName): void {
        if (!self::$dispatchPluginEvents) {
            return;
        }
        /** @var ResourceTransitionCommand $command */
        $command = $event->getCommand();
        $resource = $command->getResource();
        if ($resource->hasWorkflow()) {
            $configs = $this->resourceWorkflowPlugins->getPluginsConfig($command->getTransition()->getToIds(), $resource->getWorkflow());
            foreach ($configs as $pluginConfig) {
                $this->resourceWorkflowPlugins->getPlugin($pluginConfig)->{$methodName}($event, $pluginConfig);
            }
        }
    }
}
