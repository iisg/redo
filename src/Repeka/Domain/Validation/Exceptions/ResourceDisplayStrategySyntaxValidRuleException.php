<?php
namespace Repeka\Domain\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class ResourceDisplayStrategySyntaxValidRuleException extends ValidationException {
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'invalidResourceDisplayStrategy',
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => 'the resource display strategy is valid',
        ],
    ];
}
