<?php
namespace Repeka\Domain\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class MetadataGroupExistsRuleException extends ValidationException {
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => "Metadata group does not exist",
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => 'Metadata group exists',
        ],
    ];
}
