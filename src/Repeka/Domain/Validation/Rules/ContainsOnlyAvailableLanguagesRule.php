<?php
namespace Repeka\Domain\Validation\Rules;

use Repeka\Domain\Repository\LanguageRepository;
use Respect\Validation\Rules\AbstractRule;
use Respect\Validation\Validator;

class ContainsOnlyAvailableLanguagesRule extends AbstractRule {
    /** @var Validator */
    private $onlyAvailableLanguagesValidator;

    public function __construct(LanguageRepository $languageRepository) {
        $languageValidators = array_map(
            function (string $languageCode) {
                return Validator::key($languageCode, null, false);
            },
            $languageRepository->getAvailableLanguageCodes()
        );
        $this->onlyAvailableLanguagesValidator = call_user_func_array([Validator::arrayType(), 'keySet'], $languageValidators);
    }

    public function validate($input) {
        return $this->onlyAvailableLanguagesValidator->validate($input);
    }
}
