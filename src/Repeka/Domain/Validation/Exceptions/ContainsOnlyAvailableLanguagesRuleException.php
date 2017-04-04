<?php
namespace Repeka\Domain\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class ContainsOnlyAvailableLanguagesRuleException extends ValidationException {
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => '{{name}} contains a language that does not exist.',
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => '{{name}} contains only languages that exist.',
        ],
    ];
}
