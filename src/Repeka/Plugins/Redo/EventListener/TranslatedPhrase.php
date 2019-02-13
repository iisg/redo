<?php
namespace Repeka\Plugins\Redo\EventListener;

class TranslatedPhrase {
    private $phrase;
    private $language;

    public function __construct(string $phrase, string $language) {
        $this->phrase = $phrase;
        $this->language = $language;
    }

    public function getTranslated(): string {
        return $this->phrase;
    }

    public function getSourceLanguage(): string {
        return $this->language;
    }
}
