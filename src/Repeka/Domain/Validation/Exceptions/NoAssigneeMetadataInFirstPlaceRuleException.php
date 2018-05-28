<?php
namespace Repeka\Domain\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class NoAssigneeMetadataInFirstPlaceRuleException extends ValidationException {
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'First place cannot contain assignee metadata',
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => 'First place contains assignee metadata',
        ],
    ];
}
