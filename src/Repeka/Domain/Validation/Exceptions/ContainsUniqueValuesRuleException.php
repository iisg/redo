<?php
namespace Repeka\Domain\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class ContainsUniqueValuesRuleException extends ValidationException {
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => '{{name}} contains duplicates.',
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => '{{name}} contains only unique values.',
        ],
    ];
}
