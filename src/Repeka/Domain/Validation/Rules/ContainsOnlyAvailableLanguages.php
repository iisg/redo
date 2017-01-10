<?php
namespace Repeka\Domain\Validation\Rules;

use Repeka\Domain\Validation\Validator;
use Respect\Validation\Rules\AbstractRule;

class ContainsOnlyAvailableLanguages extends AbstractRule {
    /** @var Validator */
    private $onlyAvailableLanguagesValidator;

    public function __construct(array $availableLanguages) {
        $languageValidators = array_map(function (string $languageCode) {
            return Validator::key($languageCode, null, false);
        }, $availableLanguages);
        $this->onlyAvailableLanguagesValidator = call_user_func_array([Validator::arrayType(), 'keySet'], $languageValidators);
    }

    public function validate($input) {
        return $this->onlyAvailableLanguagesValidator->validate($input);
    }
}
