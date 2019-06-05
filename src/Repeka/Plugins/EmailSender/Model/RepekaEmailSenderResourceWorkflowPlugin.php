<?php
namespace Repeka\Plugins\EmailSender\Model;

use Repeka\Domain\Cqrs\Event\BeforeCommandHandlingEvent;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlacePluginConfiguration;
use Repeka\Domain\Service\FileSystemDriver;
use Repeka\Domain\Service\ResourceDisplayStrategyEvaluator;
use Repeka\Domain\Service\ResourceFileStorage;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;
use Repeka\Domain\Workflow\ResourceWorkflowPlugin;
use Repeka\Domain\Workflow\ResourceWorkflowPluginConfigurationOption;

class RepekaEmailSenderResourceWorkflowPlugin extends ResourceWorkflowPlugin {
    private $mailer;
    private $displayStrategyEvaluator;
    /** @var ResourceFileStorage */
    private $resourceFileStorage;
    /** @var FileSystemDriver */
    private $fileSystemDriver;

    public function __construct(
        ResourceDisplayStrategyEvaluator $displayStrategyEvaluator,
        EmailSender $mailer,
        ResourceFileStorage $resourceFileStorage,
        FileSystemDriver $fileSystemDriver
    ) {
        $this->mailer = $mailer;
        $this->displayStrategyEvaluator = $displayStrategyEvaluator;
        $this->resourceFileStorage = $resourceFileStorage;
        $this->fileSystemDriver = $fileSystemDriver;
    }

    public function beforeEnterPlace(BeforeCommandHandlingEvent $event, ResourceWorkflowPlacePluginConfiguration $config) {
        /** @var ResourceTransitionCommand $command */
        $command = $event->getCommand();
        $resource = $command->getResource();
        $email = $this->displayStrategyEvaluator->render($resource, $config->getConfigValue('email'));
        $subject = $this->displayStrategyEvaluator->render($resource, $config->getConfigValue('subject'));
        $message = $this->displayStrategyEvaluator->render($resource, $config->getConfigValue('message'));
        $message = $this->mailer->newMessage()
            ->setTo($email)
            ->setSubject($subject)
            ->setBody($message);
        if ($attachments = $config->getConfigValue('attachments')) {
            $attachments = $this->displayStrategyEvaluator->render($resource, $config->getConfigValue('attachments'));
            $attachments = array_filter(array_map('trim', explode(',', $attachments)));
            foreach ($attachments as $attachmentPath) {
                $fileSystemPath = $this->resourceFileStorage->getFileSystemPath($resource, $attachmentPath);
                if ($this->fileSystemDriver->exists($fileSystemPath)) {
                    $message->addAttachment($fileSystemPath);
                }
            }
        }
        $message->send();
    }

    /**
     * @return ResourceWorkflowPluginConfigurationOption[]
     */
    public function getConfigurationOptions(): array {
        return [
            new ResourceWorkflowPluginConfigurationOption('email', MetadataControl::TEXT()),
            new ResourceWorkflowPluginConfigurationOption('subject', MetadataControl::TEXT()),
            new ResourceWorkflowPluginConfigurationOption('message', MetadataControl::TEXTAREA()),
            new ResourceWorkflowPluginConfigurationOption('attachments', MetadataControl::TEXTAREA()),
        ];
    }
}
