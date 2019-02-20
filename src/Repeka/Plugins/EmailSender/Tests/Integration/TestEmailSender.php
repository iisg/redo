<?php
namespace Repeka\Plugins\EmailSender\Tests\Integration;

use Repeka\Plugins\EmailSender\Model\EmailSender;

class TestEmailSender implements EmailSender {

    /** @var array \Swift_Message[] */
    public static $sentMessages = [];

    public function newMessage(): \Swift_Message {
        return new \Swift_Message();
    }

    public function send(\Swift_Message $message): int {
        self::$sentMessages[] = $message;
        return count($message->getTo());
    }
}
