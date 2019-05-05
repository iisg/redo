<?php
namespace Repeka\Plugins\EmailSender\Model;

use Repeka\Domain\Cqrs\Event\BeforeCommandHandlingEvent;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlacePluginConfiguration;
use Repeka\Domain\Service\ResourceDisplayStrategyEvaluator;
use Repeka\Domain\Workflow\ResourceWorkflowPlugin;
use Repeka\Domain\Workflow\ResourceWorkflowPluginConfigurationOption;

class RepekaEmailSenderResourceWorkflowPlugin extends ResourceWorkflowPlugin {
    private $mailer;
    private $displayStrategyEvaluator;

    public function __construct(ResourceDisplayStrategyEvaluator $displayStrategyEvaluator, EmailSender $mailer) {
        $this->mailer = $mailer;
        $this->displayStrategyEvaluator = $displayStrategyEvaluator;
    }

    public function beforeEnterPlace(BeforeCommandHandlingEvent $event, ResourceWorkflowPlacePluginConfiguration $config) {
        $command = $event->getCommand();
        $resource = $command->getContents();
        $email = $this->displayStrategyEvaluator->render($resource, $config->getConfigValue('email'));
        $subject = $this->displayStrategyEvaluator->render($resource, $config->getConfigValue('subject'));
        $message = $this->displayStrategyEvaluator->render($resource, $config->getConfigValue('message'));
        $this->mailer->newMessage()
            ->setTo($email)
            ->setSubject($subject)
            ->setBody($message)
            ->send();
    }

    /**
     * @return ResourceWorkflowPluginConfigurationOption[]
     */
    public function getConfigurationOptions(): array {
        return [
            new ResourceWorkflowPluginConfigurationOption('email', MetadataControl::TEXT()),
            new ResourceWorkflowPluginConfigurationOption('subject', MetadataControl::TEXT()),
            new ResourceWorkflowPluginConfigurationOption('message', MetadataControl::TEXTAREA()),
        ];
    }
}
