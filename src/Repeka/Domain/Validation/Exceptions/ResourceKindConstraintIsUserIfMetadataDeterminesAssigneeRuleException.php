<?php
namespace Repeka\Domain\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class ResourceKindConstraintIsUserIfMetadataDeterminesAssigneeRuleException extends ValidationException {
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'metadataConstrainedToUsers',
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => "", // this just doesn't make any sense, don't bother figuring it out
        ],
    ];
}
