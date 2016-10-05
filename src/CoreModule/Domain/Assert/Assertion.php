<?php
declare (strict_types = 1);
namespace Repeka\CoreModule\Domain\Assert;

use Assert\Assertion as BaseAssertion;
use Repeka\CoreModule\Domain\Exception\AssertionFailedException;

class Assertion extends BaseAssertion {
    protected static $exceptionClass = AssertionFailedException::class;
}