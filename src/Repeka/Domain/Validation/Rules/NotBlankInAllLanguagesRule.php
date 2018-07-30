<?php
namespace Repeka\Domain\Validation\Rules;

use Repeka\Domain\Repository\LanguageRepository;
use Respect\Validation\Rules\AbstractRule;
use Respect\Validation\Validator;

class NotBlankInAllLanguagesRule extends AbstractRule {
    /** @var LanguageRepository */
    private $languageRepository;

    public function __construct(LanguageRepository $languageRepository) {
        $this->languageRepository = $languageRepository;
    }

    public function validate($input) {
        $availableLanguages = $this->languageRepository->getAvailableLanguageCodes();
        $requiredLanguagesChecks = array_map(
            function ($code) {
                return Validator::key($code, Validator::notBlank());
            },
            $availableLanguages
        );
        $requiredLanguagesCount = count($availableLanguages);
        return Validator::allOf(
            Validator::arrayType()->length($requiredLanguagesCount, $requiredLanguagesCount),
            $requiredLanguagesChecks
        )->validate($input);
    }
}
