<?php
namespace Repeka\Application;

use Repeka\Application\DependencyInjection\MetadataConstraintCompilerPass;
use Repeka\Application\ParamConverter\MetadataValueProcessor\MetadataValueProcessorPass;
use Repeka\Domain\Cqrs\Event\CommandEventsListener;
use Repeka\Domain\Workflow\ResourceWorkflowPlugin;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class RepekaBundle extends Bundle {
    public function build(ContainerBuilder $container) {
        $container->addCompilerPass(new MetadataValueProcessorPass());
        $container->registerForAutoconfiguration(ResourceWorkflowPlugin::class)->addTag('repeka.workflow_plugin');
        $container->registerForAutoconfiguration(CommandEventsListener::class)->addTag('repeka.command_events_listener');
    }
}
