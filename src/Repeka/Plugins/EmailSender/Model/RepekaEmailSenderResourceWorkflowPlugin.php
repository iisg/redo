<?php
namespace Repeka\Plugins\EmailSender\Model;

use Repeka\Domain\Cqrs\Event\BeforeCommandHandlingEvent;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlacePluginConfiguration;
use Repeka\Domain\Service\ResourceDisplayStrategyEvaluator;
use Repeka\Domain\Workflow\ResourceWorkflowPlugin;
use Repeka\Domain\Workflow\ResourceWorkflowPluginConfigurationOption;
use Repeka\Plugins\EmailSender\Util\EmailUtils;

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
        $emailTab = EmailUtils::getValidEmailAddresses($email);
        if ($emailTab) {
            try {
                $message = $this->mailer->newMessage()
                    ->setTo($emailTab)
                    ->setSubject($subject)
                    ->setBody($message);
                if (!$this->mailer->send($message)) {
                    $this->newAuditEntry($event, "not_sent");
                }
            } catch (\Exception $e) {
                $this->newAuditEntry($e, 'failure', ['message' => $e->getMessage()]);
            }
        } else {
            $this->newAuditEntry($event, "bad_email");
        }
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
