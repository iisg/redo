<?php
namespace Repeka\Application;

use Repeka\Domain\Cqrs\Event\CommandEventsListener;
use Repeka\Domain\Workflow\ResourceWorkflowPlugin;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class RepekaBundle extends Bundle {
    public function build(ContainerBuilder $container) {
        $container->registerForAutoconfiguration(ResourceWorkflowPlugin::class)->addTag('repeka.workflow_plugin');
        $container->registerForAutoconfiguration(CommandEventsListener::class)->addTag('repeka.command_events_listener');
    }
}
