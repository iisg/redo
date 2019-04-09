<?php
namespace Repeka\Plugins\EmailSender\Tests\Integration;

use Repeka\Domain\Service\ResourceDisplayStrategyEvaluator;
use Repeka\Plugins\EmailSender\Model\EmailMessage;
use Repeka\Plugins\EmailSender\Model\EmailSender;

class TestEmailSender implements EmailSender {

    /** @var array \Swift_Message[] */
    public static $sentMessages = [];
    /** @var ResourceDisplayStrategyEvaluator */
    private $displayStrategyEvaluator;

    public function __construct(ResourceDisplayStrategyEvaluator $displayStrategyEvaluator) {
        $this->displayStrategyEvaluator = $displayStrategyEvaluator;
    }

    public function newMessage(): EmailMessage {
        return new EmailMessage($this, $this->displayStrategyEvaluator);
    }

    public function send(\Swift_Message $message): int {
        self::$sentMessages[] = $message;
        return count($message->getTo());
    }
}
