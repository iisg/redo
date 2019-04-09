<?php
namespace Repeka\Plugins\EmailSender\Tests\Model;

use Repeka\Domain\Service\ResourceDisplayStrategyEvaluator;
use Repeka\Plugins\EmailSender\Model\EmailMessage;
use Repeka\Plugins\EmailSender\Model\EmailSender;

class EmailMessageTest extends \PHPUnit_Framework_TestCase {
    /** @dataProvider getValidEmailAddressesExamples */
    public function testGetValidEmailAddresses(string $emails, array $expected) {
        $strategyEvaluator = $this->createMock(ResourceDisplayStrategyEvaluator::class);
        $strategyEvaluator->method('render')->willReturnArgument(1);
        $message = new EmailMessage($this->createMock(EmailSender::class), $strategyEvaluator);
        $message->setTo($emails);
        $this->assertEquals($expected, array_keys($message->getTo()));
    }

    public function getValidEmailAddressesExamples() {
        return [
            ['john@doe.com', ['john@doe.com']],
            ['john@doe', []],
            ['', []],
            ['john@doe.com, jane@doe.com', ['john@doe.com', 'jane@doe.com']],
            ['johndoe.com, jane@doe.com', ['jane@doe.com']],
            ['jane@doe.com, jane@doe.com', ['jane@doe.com']],
        ];
    }
}
