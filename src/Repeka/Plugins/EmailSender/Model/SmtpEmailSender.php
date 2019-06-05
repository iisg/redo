<?php
namespace Repeka\Plugins\EmailSender\Model;

use Psr\Log\LoggerInterface;
use Repeka\Domain\Factory\Audit;
use Repeka\Domain\Service\ResourceDisplayStrategyEvaluator;
use Repeka\Domain\Utils\StringUtils;
use Repeka\Domain\Workflow\ResourceWorkflowPlugin;

/** @SuppressWarnings(PHPMD.ExcessiveParameterList) */
class SmtpEmailSender implements EmailSender {
    private $fromEmail;
    private $fromName;
    private $mailer;
    /** @var ResourceDisplayStrategyEvaluator */
    private $resourceDisplayStrategyEvaluator;
    /** @var string */
    private $smtpHost;
    /** @var int */
    private $smtpPort;
    /** @var string */
    private $smtpUsername;
    /** @var string */
    private $smtpPassword;
    /** @var string|null */
    private $smtpEncryption;
    /** @var LoggerInterface */
    private $logger;
    /** @var Audit */
    private $audit;

    public function __construct(
        string $smtpHost,
        int $smtpPort,
        string $smtpUsername,
        string $smtpPassword,
        ?string $smtpEncryption,
        string $fromEmail,
        string $fromName,
        ResourceDisplayStrategyEvaluator $resourceDisplayStrategyEvaluator,
        LoggerInterface $logger,
        Audit $audit
    ) {
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
        $this->smtpHost = $smtpHost;
        $this->smtpPort = $smtpPort;
        $this->smtpUsername = $smtpUsername;
        $this->smtpPassword = $smtpPassword;
        $this->smtpEncryption = $smtpEncryption;
        $this->resourceDisplayStrategyEvaluator = $resourceDisplayStrategyEvaluator;
        $this->logger = $logger;
        $this->audit = $audit;
    }

    public function newMessage(): EmailMessage {
        return (new EmailMessage($this, $this->resourceDisplayStrategyEvaluator))
            ->setFrom([$this->fromEmail => $this->fromName]);
    }

    public function send(EmailMessage $emailMessage): int {
        $message = $emailMessage->toSwiftMessage();
        try {
            $sent = $this->getMailer()->send($message);
            $this->auditEmailSentSuccess($message, $sent);
            return $sent;
        } catch (\Exception $e) {
            $this->auditEmailSentFailure($message, $e);
            throw $e;
        }
    }

    private function getMailer(): \Swift_Mailer {
        if (!$this->mailer) {
            $transport = (new \Swift_SmtpTransport($this->smtpHost, $this->smtpPort))
                ->setUsername($this->smtpUsername)
                ->setPassword($this->smtpPassword);
            if ($this->smtpEncryption) {
                $transport->setEncryption($this->smtpEncryption);
            }
            $this->mailer = new \Swift_Mailer($transport);
        }
        return $this->mailer;
    }

    private function auditEmailSentSuccess(\Swift_Message $message, int $sentCount) {
        $this->auditEmailSent($message, 'success', ['sentCount' => $sentCount], $sentCount > 0);
    }

    private function auditEmailSentFailure(\Swift_Message $message, \Exception $e) {
        $this->logger->error(
            'Could not send e-mail message: ' . $e->getMessage(),
            ['stackTrace' => $e->getTraceAsString()]
        );
        $this->auditEmailSent($message, 'failure', ['exceptionMessage' => StringUtils::fixUtf8($e->getMessage())], false);
    }

    /** @SuppressWarnings("PHPMD.BooleanArgumentFlag") */
    private function auditEmailSent(\Swift_Message $message, $eventName, $data = [], $successful = true) {
        $pluginName = ResourceWorkflowPlugin::getNameFromClassName(RepekaEmailSenderResourceWorkflowPlugin::class);
        $data = array_merge(
            [
                'recipients' => implode(', ', array_keys($message->getTo())),
                'subject' => StringUtils::fixUtf8($message->getSubject()),
            ],
            $data
        );
        ResourceWorkflowPlugin::newPluginAuditEntry($this->audit, $pluginName, null, $eventName, $data, $successful);
    }
}
