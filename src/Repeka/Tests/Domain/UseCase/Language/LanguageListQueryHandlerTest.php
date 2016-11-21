<?php
namespace Repeka\Tests\Domain\UseCase\Language;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Entity\Language;
use Repeka\Domain\Repository\LanguageRepository;
use Repeka\Domain\UseCase\Language\LanguageListQueryHandler;

class LanguageListQueryHandlerTest extends \PHPUnit_Framework_TestCase {
    /** @var PHPUnit_Framework_MockObject_MockObject */
    private $languageRepository;
    /** @var LanguageListQueryHandler */
    private $handler;

    protected function setUp() {
        $this->languageRepository = $this->createMock(LanguageRepository::class);
        $this->handler = new LanguageListQueryHandler($this->languageRepository);
    }

    public function testGettingTheList() {
        $languageList = [$this->createMock(Language::class)];
        $this->languageRepository->expects($this->once())->method('findAll')->willReturn($languageList);
        $returnedList = $this->handler->handle();
        $this->assertSame($languageList, $returnedList);
    }
}
