<?php
namespace Repeka\Domain\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class ResourceDoesNotContainDuplicatedFilenamesRuleException extends ValidationException {
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => '{{name}} contains duplicated filenames: {{duplicatedFilenames}}.',
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => '{{name}} does not contain duplicated filenames: {{duplicatedFilenames}}.',
        ],
    ];
}
