<?php
namespace Repeka\DataModule\Domain\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class NotBlankInLanguageException extends ValidationException {
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => '{{name}} must be set in the main language.',
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => '{{name}} must not be set in the main language.',
        ],
    ];
}
