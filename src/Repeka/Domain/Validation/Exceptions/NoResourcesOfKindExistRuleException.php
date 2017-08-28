<?php
namespace Repeka\Domain\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class NoResourcesOfKindExistRuleException extends ValidationException {
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => "{{name}} resource kind is used by some resources.",
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => "{{name}} resource kind is used by no resources.",
        ],
    ];
}
