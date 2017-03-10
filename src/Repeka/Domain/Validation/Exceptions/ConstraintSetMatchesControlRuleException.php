<?php
namespace Repeka\Domain\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class ConstraintSetMatchesControlRuleException extends ValidationException {
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => "{{name}} doesn't have required constraint set.",
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => '{{name}} has required constraint set.',
        ],
    ];
}
