<?php
namespace Repeka\Domain\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class TeaserVisibilityWiderOrEqualToFullVisibilityRuleException extends ValidationException {
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => "User or group that can see a resource must have permission to see the teaser",
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => "User or group that can see a resource must have permission to see the teaser",
        ],
    ];
}
