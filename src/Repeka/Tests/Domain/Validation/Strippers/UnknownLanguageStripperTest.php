<?php
namespace Repeka\Tests\Domain\Validation\Strippers;

use Repeka\Domain\Validation\Strippers\UnknownLanguageStripper;
use Repeka\Tests\Traits\StubsTrait;

class UnknownLanguageStripperTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;
    /** @var  UnknownLanguageStripper */
    private $unknownLanguageStripper;

    protected function setUp() {
        $languageRepository = $this->createLanguageRepositoryMock(['PL']);
        $this->unknownLanguageStripper = new UnknownLanguageStripper($languageRepository);
    }

    public function testFiltersUnknownLanguages() {
        $result = $this->unknownLanguageStripper->removeUnknownLanguages(['PL' => 'Test', 'EN' => 'Test']);
        $this->assertEquals(['PL' => 'Test'], $result);
    }
}
