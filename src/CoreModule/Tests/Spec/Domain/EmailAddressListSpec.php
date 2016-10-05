<?php
namespace Spec\Repeka\CoreModule\Domain;

use Repeka\CoreModule\Domain\EmailAddress;
use Repeka\CoreModule\Domain\EmailAddressList;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class EmailAddressListSpec
 * @package Spec\Repeka\CoreModule\Domain
 * @mixin EmailAddressList
 */
class EmailAddressListSpec extends ObjectBehavior {
    const EMAIL_ADDRESS_FIRST = 'first@test.pl';
    const EMAIL_ADDRESS_SECOND = 'second@test.pl';

    function it_is_initializable() {
        $this->shouldHaveType(EmailAddressList::class);
    }

    function it_is_countable() {
        $this->shouldBeAnInstanceOf(\Countable::class);
    }

    function it_is_iterator() {
        $this->shouldBeAnInstanceOf(\Iterator::class);
    }

    function it_adds_email_address() {
        $this->add($this->getFirstEmailAddress());
        $this->count()->shouldBe(1);
    }

    function it_removes_email_address() {
        $this->add($this->getFirstEmailAddress());
        $secondEmail = $this->getSecondEmailAddress();
        $this->add($secondEmail);
        $this->remove($secondEmail);
        $this->count()->shouldBe(1);
    }

    function it_is_not_valid_if_is_empty() {
        $this->valid()->shouldBe(false);
    }

    function it_is_valid_if_not_empty() {
        $this->add($this->getFirstEmailAddress());
        $this->valid()->shouldBe(true);
    }

    function it_returns_current_element() {
        $firstEmail = $this->getFirstEmailAddress();
        $this->add($firstEmail);
        $this->current()->shouldBe($firstEmail);
    }

    function it_gets_next_element() {
        $this->add($this->getFirstEmailAddress());
        $secondEmail = $this->getSecondEmailAddress();
        $this->add($secondEmail);
        $this->next();
        $this->current()->shouldBe($secondEmail);
    }

    function it_gets_current_index() {
        $this->add($this->getFirstEmailAddress());
        $this->add($this->getSecondEmailAddress());
        $this->key()->shouldBe(0);
        $this->next();
        $this->key()->shouldBe(1);
    }

    function it_is_not_valid_if_exceed() {
        $this->add($this->getFirstEmailAddress());
        $this->next();
        $this->valid()->shouldBe(false);
    }

    function it_rewinds_index() {
        $this->add($this->getFirstEmailAddress());
        $this->add($this->getSecondEmailAddress());
        $this->next();
        $this->next();
        $this->key()->shouldBe(2);
        $this->rewind();
        $this->key()->shouldBe(0);
    }

    private function getFirstEmailAddress() {
        return new EmailAddress(self::EMAIL_ADDRESS_FIRST);
    }

    private function getSecondEmailAddress() {
        return new EmailAddress(self::EMAIL_ADDRESS_SECOND);
    }
}
