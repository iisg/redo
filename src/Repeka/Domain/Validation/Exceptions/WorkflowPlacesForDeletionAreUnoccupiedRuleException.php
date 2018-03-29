<?php
namespace Repeka\Domain\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class WorkflowPlacesForDeletionAreUnoccupiedRuleException extends ValidationException {
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'Workflow places you want to delete are occupied',
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => 'The workflow places you want to delete are unoccupied',
        ],
    ];
}
