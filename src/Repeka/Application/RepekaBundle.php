<?php
namespace Repeka\Application;

use Repeka\Application\Authentication\TokenAuthenticator;
use Repeka\Application\Command\Cyclic\CyclicCommand;
use Repeka\Application\Security\Voters\FileDownloadVoter;
use Repeka\Application\Twig\FrontendConfigProvider;
use Repeka\Domain\Cqrs\Event\CommandEventsListener;
use Repeka\Domain\Metadata\MetadataValueAdjuster\MetadataValueAdjuster;
use Repeka\Domain\Validation\MetadataConstraints\AbstractMetadataConstraint;
use Repeka\Domain\Workflow\ResourceWorkflowPlugin;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class RepekaBundle extends Bundle {
    public function build(ContainerBuilder $container) {
        $container->registerForAutoconfiguration(ResourceWorkflowPlugin::class)->addTag('repeka.workflow_plugin');
        $container->registerForAutoconfiguration(CommandEventsListener::class)->addTag('repeka.command_events_listener');
        $container->registerForAutoconfiguration(CyclicCommand::class)->addTag('repeka.cyclic_command');
        $container->registerForAutoconfiguration(TokenAuthenticator::class)->addTag('repeka.token_authenticator');
        $container->registerForAutoconfiguration(AbstractMetadataConstraint::class)->addTag('repeka.metadata_constraint');
        $container->registerForAutoconfiguration(MetadataValueAdjuster::class)->addTag('repeka.metadata_value_adjuster');
        $container->registerForAutoconfiguration(FileDownloadVoter::class)->addTag('repeka.file_download_voter');
        $container->registerForAutoconfiguration(FrontendConfigProvider::class)->addTag('repeka.frontend_config_provider');
    }
}
