<?php
namespace Repeka\Plugins\EmailSender\Model;

interface EmailSender {
    public function newMessage(): EmailMessage;

    public function send(\Swift_Message $message): int;
}
