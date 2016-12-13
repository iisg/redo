<?php
namespace Repeka\Domain\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class NotBlankInAllLanguagesException extends ValidationException {
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => '{{name}} must be set in all languages.',
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => '{{name}} must not be set in all languages.',
        ],
    ];
}
