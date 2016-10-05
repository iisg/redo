<?php
namespace Spec\Repeka\CoreModule\Domain;

use Repeka\CoreModule\Domain\UUID;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Repeka\CoreModule\Domain\Exception\AssertionFailedException;

/**
 * Class UUIDSpec
 * @package Spec\Repeka\CoreModule\Domain
 * @mixin UUID
 */
class UUIDSpec extends ObjectBehavior {
    const UUID = '7d6fbc36-6382-4152-bd96-44f54aad35ba';

    function let() {
        $this->beConstructedWith(self::UUID);
    }

    function it_is_initializable() {
        $this->shouldHaveType(UUID::class);
    }

    function it_throws_exception_when_invalid_format() {
        $this->shouldThrow(AssertionFailedException::class)->during('__construct', ['invalid_format']);
    }

    function it_has_string_representation() {
        $this->__toString()->shouldBe(self::UUID);
    }
}
