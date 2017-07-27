<?php
namespace Repeka\Domain\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class ResourceClassExistsRuleException extends ValidationException {
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'Resource class - {{name}} - not exists.',
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => 'Resource class - {{name}} - exists.',
        ],
    ];
}
