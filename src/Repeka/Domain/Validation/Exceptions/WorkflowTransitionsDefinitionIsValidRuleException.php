<?php
namespace Repeka\Domain\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class WorkflowTransitionsDefinitionIsValidRuleException extends ValidationException {
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => "{{name}} contains invalid constraint arguments.",
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => '{{name}} contains valid constraint arguments.',
        ],
    ];
}
