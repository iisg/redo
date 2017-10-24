<?php
namespace Repeka\Domain\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class UserRoleHasNoUsageRuleException extends ValidationException {
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'user role is in use',
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => 'user role is not in use',
        ],
    ];
}
