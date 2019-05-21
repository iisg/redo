<?php
namespace Repeka\Plugins\EmailSender\Model;

use Repeka\Domain\Service\ResourceDisplayStrategyEvaluator;

class EmailMessage {
    private $message;

    /** @var EmailSender */
    private $emailSender;
    /** @var ResourceDisplayStrategyEvaluator */
    private $strategyEvaluator;

    public function __construct(EmailSender $emailSender, ResourceDisplayStrategyEvaluator $strategyEvaluator) {
        $this->emailSender = $emailSender;
        $this->strategyEvaluator = $strategyEvaluator;
        $this->message = new \Swift_Message();
    }

    public function setFrom(array $froms): self {
        $this->message->setFrom($froms);
        return $this;
    }

    public function setTo(string $addresses, array $context = []): self {
        $addresses = $this->strategyEvaluator->render(null, $addresses, null, $context);
        $addresses = $this->getValidEmailAddresses($addresses);
        $this->message->setTo($addresses);
        return $this;
    }

    public function getTo(): array {
        return $this->message->getTo();
    }

    public function setSubject(string $subject, ?array $context = []): self {
        if ($context !== null) {
            $subject = $this->strategyEvaluator->render(null, $subject, null, $context);
        }
        $this->message->setSubject($subject);
        return $this;
    }

    public function setBody(string $body, ?array $context = []): self {
        if ($context !== null) {
            $body = $this->strategyEvaluator->render(null, $body, null, $context);
        }
        $this->message->setBody($body);
        return $this;
    }

    public function setTemplate(string $path, array $context = []): self {
        $template = '{% include "' . $path . '" %}';
        if (!$this->message->getSubject()) {
            $rendered = $this->strategyEvaluator->render(null, $template, null, $context);
            list($subject, $body) = explode("\n", $rendered, 2);
            $this->setSubject(trim($subject), null);
            return $this->setBody(trim($body), null);
        } else {
            return $this->setBody($template, $context);
        }
    }

    public function send(): int {
        return $this->emailSender->send($this->message);
    }

    public function sendSafe(): int {
        try {
            return $this->emailSender->send($this->message);
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function toSwiftMessage(): \Swift_Message {
        return $this->message;
    }

    private function getValidEmailAddresses(string $emails): array {
        return array_values(
            array_unique(
                array_map(
                    'trim',
                    array_filter(
                        explode(',', $emails),
                        function ($email) {
                            return filter_var(trim($email), FILTER_VALIDATE_EMAIL);
                        }
                    )
                )
            )
        );
    }
}
