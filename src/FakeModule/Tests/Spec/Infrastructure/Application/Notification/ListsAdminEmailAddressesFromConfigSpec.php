<?php
namespace Spec\Repeka\FakeModule\Infrastructure\Application\Notification;

use Repeka\FakeModule\Infrastructure\Application\Notification\ListsAdminEmailAddressesFromConfig;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Repeka\CoreModule\Domain\EmailAddressList;
use Repeka\FakeModule\Application\Notification\ListsAdminEmailAddresses;

/**
 * Class ListAdminEmailAddressFromConfigSpec
 * @package Spec\Repeka\FakeModule\Infrastructure\Application\Notification
 * @mixin ListsAdminEmailAddressesFromConfig
 */
class ListsAdminEmailAddressesFromConfigSpec extends ObjectBehavior {
    const EMAIL_ADDRESS_FIRST = 'first@test.pl';
    const EMAIL_ADDRESS_SECOND = 'second@test.pl';

    function let() {
        $this->beConstructedWith([self::EMAIL_ADDRESS_FIRST, self::EMAIL_ADDRESS_SECOND]);
    }

    function it_is_initializable() {
        $this->shouldHaveType(ListsAdminEmailAddressesFromConfig::class);
    }

    function it_implements_list_admin_email_address() {
        $this->shouldImplement(ListsAdminEmailAddresses::class);
    }

    function it_returns_list_email_address() {
        $listEmailAddress = $this->getAdminEmailAddresses();
        $listEmailAddress->shouldBeAnInstanceOf(EmailAddressList::class);
        $listEmailAddress->count()->shouldBe(2);
    }
}
