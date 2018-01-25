<?php
namespace Repeka\Domain\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class ResourceContentsCorrectStructureRuleException extends ValidationException {
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => "resource contents have invalid structure",
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => 'resource contents have valid structure',
        ],
    ];
}
