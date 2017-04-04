<?php
namespace Repeka\Domain\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class IsValidControlRuleException extends ValidationException {
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => '{{name}} is not a valid control.',
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => '{{name}} is a valid control.',
        ],
    ];
}
