<?php
namespace Repeka\Domain\Validation\Rules;

use Repeka\Domain\Repository\LanguageRepository;
use Respect\Validation\Rules\AbstractRule;
use Respect\Validation\Validator;

class NotBlankInAllLanguagesRule extends AbstractRule {
    private $requiredLanguagesChecks;

    private $requiredLanguagesCount;

    public function __construct(LanguageRepository $languageRepository) {
        $availableLanguages = $languageRepository->getAvailableLanguageCodes();
        $this->requiredLanguagesChecks = array_map(
            function ($code) {
                return Validator::key($code, Validator::notBlank());
            },
            $availableLanguages
        );
        $this->requiredLanguagesCount = count($availableLanguages);
    }

    public function validate($input) {
        return Validator::allOf(
            Validator::arrayType()->length($this->requiredLanguagesCount, $this->requiredLanguagesCount),
            $this->requiredLanguagesChecks
        )->validate($input);
    }
}
