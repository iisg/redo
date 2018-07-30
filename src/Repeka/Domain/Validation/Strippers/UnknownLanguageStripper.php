<?php
namespace Repeka\Domain\Validation\Strippers;

use Repeka\Domain\Repository\LanguageRepository;

class UnknownLanguageStripper {

    /** @var LanguageRepository */
    private $languageRepository;

    public function __construct(LanguageRepository $languageRepository) {
        $this->languageRepository = $languageRepository;
    }

    public function removeUnknownLanguages(array $content): array {
        return array_filter(
            $content,
            function ($key) {
                return in_array($key, $this->languageRepository->getAvailableLanguageCodes());
            },
            ARRAY_FILTER_USE_KEY
        );
    }
}
