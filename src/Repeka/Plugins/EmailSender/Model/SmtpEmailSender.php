<?php
namespace Repeka\Plugins\EmailSender\Model;

use Psr\Log\LoggerInterface;
use Repeka\Domain\Service\ResourceDisplayStrategyEvaluator;

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

    public function __construct(
        string $smtpHost,
        int $smtpPort,
        string $smtpUsername,
        string $smtpPassword,
        ?string $smtpEncryption,
        string $fromEmail,
        string $fromName,
        ResourceDisplayStrategyEvaluator $resourceDisplayStrategyEvaluator,
        LoggerInterface $logger
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
    }

    public function newMessage(): EmailMessage {
        return (new EmailMessage($this, $this->resourceDisplayStrategyEvaluator, $this->logger))
            ->setFrom([$this->fromEmail => $this->fromName]);
    }

    public function send(\Swift_Message $message): int {
        return $this->getMailer()->send($message);
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
}
