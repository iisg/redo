<?php
namespace Repeka\Domain\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class ChildResourceKindsAreOfSameResourceClassRuleException extends ValidationException {
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => "{{name}} has elements of different resource class.",
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => 'All elements of {{name}} have same resource class.',
        ],
    ];
}
