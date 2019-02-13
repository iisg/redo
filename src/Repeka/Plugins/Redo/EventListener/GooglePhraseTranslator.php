<?php
namespace Repeka\Plugins\Redo\EventListener;

use ErrorException;
use Stichoza\GoogleTranslate\GoogleTranslate;

class GooglePhraseTranslator implements PhraseTranslator {
    private $google;

    public function __construct() {
        $this->google = new GoogleTranslate();
    }

    /**
     * Translates given phrase into given language. If no translation found null will be returned
     * @param string $phrase
     * @param string $language
     * @return TranslatedPhrase | null
     */
    public function translate(string $phrase, string $language) {
        try {
            $this->google->setTarget($language);
            $translated = $this->google->translate($phrase);
            $lastDetectedSource = $this->google->getLastDetectedSource() ?: '';
            return $translated
                ? new TranslatedPhrase($translated, $lastDetectedSource)
                : null;
        } catch (ErrorException $e) {
            return null;
        }
    }
}
