<?php
namespace Repeka\Domain\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class FlexibleDateControlMetadataCorrectStructureRuleException extends ValidationException {
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'Date control metadata has invalid structure',
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => 'Date control metadata has valid structure',
        ],
    ];
}
