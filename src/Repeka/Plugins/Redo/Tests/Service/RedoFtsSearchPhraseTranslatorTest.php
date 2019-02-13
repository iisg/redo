<?php
namespace Repeka\Plugins\Redo\Tests\Service;

use PHPUnit_Framework_TestCase;
use Repeka\Plugins\Redo\EventListener\PhraseTranslator;
use Repeka\Plugins\Redo\EventListener\TranslatedPhrase;
use Repeka\Plugins\Redo\Service\RedoFtsSearchPhraseTranslator;

class RedoFtsSearchPhraseTranslatorTest extends PHPUnit_Framework_TestCase {
    /** @var RedoFtsSearchPhraseTranslator */
    private $translator;

    protected function setUp() {
        $phraseTranslator = $this->createMock(PhraseTranslator::class);
        $translated = new TranslatedPhrase('x', 'pl');
        $translated2 = new TranslatedPhrase('y', 'en');
        $translated3 = new TranslatedPhrase('z', 'de');
        $phraseTranslator->method('translate')->willReturnOnConsecutiveCalls($translated, $translated2, $translated3);
        $this->translator = new RedoFtsSearchPhraseTranslator($phraseTranslator);
    }

    public function testSkipTranslationPhraseWithExtraCharacters() {
        $result = $this->translator->translatePhrase('kot -pies');
        $this->assertCount(0, $result);
    }

    public function testTranslatePhraseWithoutExtraCharacters() {
        $result = $this->translator->translatePhrase('kot pies');
        $this->assertCount(2, $result);
    }
}
