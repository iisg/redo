<?php
namespace Repeka\Domain\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class ResourceClassExistsRuleException extends ValidationException {
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => "resource class doesn't exist.",
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => 'resource class exists.',
        ],
    ];
}
