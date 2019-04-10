<?php
namespace Repeka\Plugins\EmailSender\Command;

use Assert\Assertion;
use Repeka\Plugins\EmailSender\Model\EmailSender;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class SendTestEmailCommand extends Command {
    /** @var EmailSender */
    private $emailSender;

    public function __construct(EmailSender $emailSender) {
        parent::__construct();
        $this->emailSender = $emailSender;
    }

    protected function configure() {
        $this
            ->setName('emailsender:test')
            ->setDescription('Sends a test e-mail message.');
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output) {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $recipient = $helper->ask($input, $output, new Question('Recipient address: '));
        Assertion::string($recipient);
        $message = $this->emailSender
            ->newMessage()
            ->setTo($recipient)
            ->setSubject('Mail test {{ "now" | date("Y-m-d H:i:s") }}')
            ->setBody('This is a test only.');
        Assertion::notEmpty($message->getTo());
        $successful = $message->send();
        $output->writeln('Message sent to ' . $successful . ' recipients.');
    }
}
