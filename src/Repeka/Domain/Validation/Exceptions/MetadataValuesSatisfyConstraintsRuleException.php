<?php
namespace Repeka\Domain\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class MetadataValuesSatisfyConstraintsRuleException extends ValidationException {
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => "{{name}} doesn't match metadata constraints."
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => "{{name}} matches metadata constraints."
        ]
    ];
}
