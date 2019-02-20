<?php
namespace Repeka\Plugins\EmailSender\Model;

class SmtpEmailSender implements EmailSender {
    private $fromEmail;
    private $fromName;
    private $mailer;
    private $message;
    private $transport;

    public function __construct(
        string $smtpHost,
        int $smtpPort,
        string $smtpUsername,
        string $smtpPassword,
        ?string $smtpEncryption,
        string $fromEmail,
        string $fromName
    ) {
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
        $this->transport = (new \Swift_SmtpTransport($smtpHost, $smtpPort))
            ->setUsername($smtpUsername)
            ->setPassword($smtpPassword);
        if ($smtpEncryption) {
            $this->transport->setEncryption($smtpEncryption);
        }
        $this->mailer = new \Swift_Mailer($this->transport);
    }

    public function newMessage(): \Swift_Message {
        $this->message = (new \Swift_Message())->setFrom([$this->fromEmail => $this->fromName]);
        return $this->message;
    }

    public function send(\Swift_Message $message): int {
        return $this->mailer->send($message);
    }

    public function getTransport(): \Swift_SmtpTransport {
        return $this->transport;
    }
}
