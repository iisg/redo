<?php
namespace Repeka\Domain\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class ConstraintArgumentsAreValidRuleException extends ValidationException {
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'invalidConstraintArguments',
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => '{{name}} contains valid constraint arguments: {{error}}',
        ],
    ];
}
