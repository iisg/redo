<?php
namespace Repeka\Domain\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class EntityExistsRuleException extends ValidationException {
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => "{{name}} contains ID of non-existent entity.",
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => '{{name}} contains ID of existing entity.',
        ],
    ];
}
