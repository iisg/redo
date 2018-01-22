<?php
namespace Repeka\Domain\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class ContainsParentMetadataRuleException extends ValidationException {
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => "parent metadata doesn't exist.",
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => 'parent metadata exists.',
        ],
    ];
}
