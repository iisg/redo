<?php
namespace Repeka\Domain\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class NotBlankInAllLanguagesRuleException extends ValidationException {
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'value must be set in all languages',
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => 'value must not be set in all languages',
        ],
    ];
}
