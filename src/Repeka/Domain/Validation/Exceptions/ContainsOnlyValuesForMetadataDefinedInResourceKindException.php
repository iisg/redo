<?php
namespace Repeka\Domain\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class ContainsOnlyValuesForMetadataDefinedInResourceKindException extends ValidationException {
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => '{{name}} contains a metadata that is not defined in the resource kind ({{originalMessage}}).',
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => '{{name}} contains a metadata that is defined in the resource kind ({{originalMessage}}).',
        ],
    ];
}
