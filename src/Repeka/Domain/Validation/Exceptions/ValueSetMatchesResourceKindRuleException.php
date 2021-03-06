<?php
namespace Repeka\Domain\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class ValueSetMatchesResourceKindRuleException extends ValidationException {
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'valuesDoNotMatchResourceKind',
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => '{{name}} contains a metadata that is defined in the resource kind ({{originalMessage}}).',
        ],
    ];
}
