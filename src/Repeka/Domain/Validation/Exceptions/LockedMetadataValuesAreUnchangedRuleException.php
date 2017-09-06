<?php
namespace Repeka\Domain\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class LockedMetadataValuesAreUnchangedRuleException extends ValidationException {
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => "{{name}} contain changed values.",
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => 'All but {{name}} contain unchanged values.',
        ],
    ];
}
