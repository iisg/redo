<?php
namespace Repeka\Plugins\Redo\Service\PhraseTranslator;

interface PhraseTranslator {
    /**
     * Translates given phrase into desired language. If no translation found null will be returned.
     * @param string $phrase
     * @param string $language
     * @return TranslatedPhrase | null
     */
    public function translate(string $phrase, string $language): ?TranslatedPhrase;
}
