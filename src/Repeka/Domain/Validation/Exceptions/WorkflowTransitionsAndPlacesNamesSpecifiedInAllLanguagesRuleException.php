<?php
namespace Repeka\Domain\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class WorkflowTransitionsAndPlacesNamesSpecifiedInAllLanguagesRuleException extends ValidationException {
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'Places or Transitions exist without a name specified in every language',
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => 'All transitions and places have names specified in all languages',
        ],
    ];
}
