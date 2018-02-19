<?php
namespace Repeka\Domain\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class ResourceMetadataSortCorrectStructureRuleException extends ValidationException {
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => "resource metadata sorts have invalid structure",
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => 'resource metadata sorts have valid structure',
        ],
    ];
}
