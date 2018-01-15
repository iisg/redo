<?php
namespace Repeka\Domain\Validation\Strippers;

use Repeka\Domain\Repository\LanguageRepository;

class UnknownLanguageStripper {

    private $availableLanguages;

    public function __construct(LanguageRepository $languageRepository) {
        $this->availableLanguages = $languageRepository->getAvailableLanguageCodes();
    }

    public function removeUnknownLanguages(array $content): array {
        return array_filter($content, function ($key) {
            return in_array($key, $this->availableLanguages);
        }, ARRAY_FILTER_USE_KEY);
    }
}
