<?php
namespace Repeka\Domain\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class WorkflowTransitionNamesMatchInAllLanguagesRuleException extends ValidationException {
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'transitionLabelsNotMatching',
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => 'Transitions {{transitionLabels}} connected to place {{placeId}}'
                . ' have all either the same name in {{matchingLanguages}}, or different in {{differentLanguages}}',
        ],
    ];
}
