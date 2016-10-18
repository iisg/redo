<?php
namespace Repeka\CoreModule\Domain;

class EmailAddressTest extends \PHPUnit_Framework_TestCase {
    public function testCreatingValidEmailAddress() {
        new EmailAddress('valid@email.pl');
    }

    /**
     * @expectedException Assert\InvalidArgumentException
     */
    public function testCreatingInvalidEmailAddress() {
        new EmailAddress('invalid');
    }

    public function testGettingDomain() {
        $email = new EmailAddress('valid@email.pl');
        $this->assertEquals('email.pl', $email->getDomainFromEmailAddress());
    }
}
