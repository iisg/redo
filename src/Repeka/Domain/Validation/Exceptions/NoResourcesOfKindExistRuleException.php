<?php
namespace Repeka\Domain\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class NoResourcesOfKindExistRuleException extends ValidationException {
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => "resources of this kind exist",
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => "resources of this kind do not exist",
        ],
    ];
}
