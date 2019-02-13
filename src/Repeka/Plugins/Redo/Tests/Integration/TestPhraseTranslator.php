<?php
namespace Repeka\Plugins\Redo\Tests\Integration;

use Repeka\Plugins\Redo\EventListener\PhraseTranslator;
use Repeka\Plugins\Redo\EventListener\TranslatedPhrase;

class TestPhraseTranslator implements PhraseTranslator {
    public function translate(string $phrase, string $language): TranslatedPhrase {
        return new TranslatedPhrase($phrase, $language);
    }
}
