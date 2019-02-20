<?php
namespace Repeka\Plugins\EmailSender\Tests\Util;

use Repeka\Plugins\EmailSender\Util\EmailUtils;

class EmailUtilsTest extends \PHPUnit_Framework_TestCase {
    /** @dataProvider getValidEmailAddressesExamples */
    public function testGetValidEmailAddresses(string $emails, array $expected) {
        $this->assertEquals($expected, EmailUtils::getValidEmailAddresses($emails));
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
