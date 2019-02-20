<?php
namespace Repeka\Plugins\EmailSender\Tests\Model;

use Repeka\Plugins\EmailSender\Model\getValidAddresses;
use Repeka\Plugins\EmailSender\Model\SmtpEmailSender;

class RepekaEmailSenderResourceWorkflowPluginTest extends \PHPUnit_Framework_TestCase {
    private $mailer;

    public function setUp() {
        $this->mailer = new SmtpEmailSender('smtp.example.pl', 25, 'john', 'doe', 'null', 'john@doe.com', 'John Doe');
    }

    public function testTransport() {
        $transport = $this->mailer->getTransport();
        $this->assertInstanceOf('Swift_Transport', $transport);
        $this->assertSame('smtp.example.pl', $transport->getHost());
        $this->assertSame(25, $transport->getPort());
        $this->assertSame('john', $transport->getUsername());
        $this->assertSame('doe', $transport->getPassword());
        $this->assertSame('null', $transport->getEncryption());
    }

    public function testMessage() {
        $message = $this->mailer->newMessage()
            ->setTo('jane@doe.com')
            ->setSubject('Hello Email')
            ->setBody('Content of the message');
        $this->assertInstanceOf('Swift_Message', $message);
        $this->assertSame('Hello Email', $message->getSubject());
        $this->assertSame('john@doe.com', key($message->getFrom()));
        $this->assertSame('jane@doe.com', key($message->getTo()));
        $this->assertSame('Content of the message', $message->getBody());
    }
}
