<?php
namespace Repeka\Plugins\Redo\Service\PhraseTranslator;

use ErrorException;
use Stichoza\GoogleTranslate\GoogleTranslate;

class GooglePhraseTranslator implements PhraseTranslator {
    private $google;

    public function __construct() {
        $this->google = new GoogleTranslate();
    }

    public function translate(string $phrase, string $language): ?TranslatedPhrase {
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
