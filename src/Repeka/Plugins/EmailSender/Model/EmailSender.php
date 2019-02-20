<?php
namespace Repeka\Plugins\EmailSender\Model;

interface EmailSender {
    public function newMessage(): \Swift_Message;

    public function send(\Swift_Message $message): int;
}
