<?php
namespace Repeka\Tests\Domain\UseCase\Language;

use Repeka\Domain\Repository\LanguageRepository;
use Repeka\Domain\UseCase\Language\LanguageDeleteCommand;
use Repeka\Domain\UseCase\Language\LanguageDeleteCommandHandler;

class LanguageDeleteCommandHandlerTest extends \PHPUnit_Framework_TestCase {
    /** @var LanguageRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $languageRepository;
    /** @var LanguageDeleteCommandHandler */
    private $handler;

    protected function setUp() {
        $this->languageRepository = $this->createMock(LanguageRepository::class);
        $this->handler = new LanguageDeleteCommandHandler($this->languageRepository);
    }

    public function testDeleting() {
        $this->languageRepository->expects($this->once())->method('delete')->with('testCode');
        $this->handler->handle(new LanguageDeleteCommand('testCode'));
    }
}
