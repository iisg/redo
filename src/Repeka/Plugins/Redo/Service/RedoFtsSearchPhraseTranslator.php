<?php
namespace Repeka\Plugins\Redo\Service;

use Repeka\Plugins\Redo\EventListener\PhraseTranslator;

class RedoFtsSearchPhraseTranslator {
    private const TARGET_LANGUAGES = ['en', 'pl', 'de'];
    public $translator;

    public function __construct(PhraseTranslator $translator) {
        $this->translator = $translator;
    }

    public function translatePhrase(string $phrase): array {
        if (!$this->isPhraseTranslatable($phrase)) {
            return [];
        }
        $phrases = [];
        if ($phrase) {
            $desiredLanguages = self::TARGET_LANGUAGES;
            foreach ($desiredLanguages as $targetLanguage) {
                $translation = $this->translator->translate($phrase, $targetLanguage);
                if ($translation) {
                    $sourceLanguage = $translation->getSourceLanguage();
                    if ($sourceLanguage != $targetLanguage) {
                        $phrases[] = $translation->getTranslated();
                    }
                    $desiredLanguages = array_diff($desiredLanguages, [$sourceLanguage]);
                }
            }
        }
         return $phrases;
    }

    private function isPhraseTranslatable(string $phrase): bool {
        return preg_match('/[\*\+\-\|\~]/i', $phrase) == 0;
    }
}
