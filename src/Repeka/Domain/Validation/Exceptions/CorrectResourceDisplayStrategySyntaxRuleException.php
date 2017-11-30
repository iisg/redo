<?php
namespace Repeka\Domain\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class CorrectResourceDisplayStrategySyntaxRuleException extends ValidationException {
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'incorrectResourceDisplayStrategy',
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => '{{name}} must not be valid resource display strategy.',
        ],
    ];
}
