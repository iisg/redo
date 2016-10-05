<?php
namespace Spec\Repeka\CoreModule\Domain;

use Repeka\CoreModule\Domain\EmailAddress;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Repeka\CoreModule\Domain\Exception\AssertionFailedException;

/**
 * Class EmailAddressSpec
 * @package Spec\Repeka\CoreModule\Domain
 * @mixin EmailAddress
 */
class EmailAddressSpec extends ObjectBehavior {
    const EMAIL_ADDRESS = 'szymon.zdebski@biblos.pk.edu.pl';

    function let() {
        $this->beConstructedWith(self::EMAIL_ADDRESS);
    }

    function it_is_initializable() {
        $this->shouldHaveType(EmailAddress::class);
    }

    function it_throws_exception_when_not_email_address() {
        $this->shouldThrow(AssertionFailedException::class)->during('__construct', ['simple_string']);
    }

    function it_has_string_representation() {
        $this->__toString()->shouldBe(self::EMAIL_ADDRESS);
    }

    function it_has_domain() {
        $this->getDomainFromEmailAddress()->shouldBe(explode('@', self::EMAIL_ADDRESS)[1]);
    }

    function it_checks_is_in_domain() {
        $this->isInDomain('pk.edu.pl')->shouldBe(true);
    }

    function it_checks_is_not_in_domain() {
        $this->isInDomain('other_domain.pl')->shouldBe(false);
    }
}
