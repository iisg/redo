<?php
namespace Repeka\Tests\Domain\UseCase\Language;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Repository\LanguageRepository;
use Repeka\Domain\UseCase\Language\LanguageCreateCommand;
use Repeka\Domain\UseCase\Language\LanguageCreateCommandHandler;

class LanguageCreateCommandHandlerTest extends \PHPUnit_Framework_TestCase {
    /** @var LanguageCreateCommand */
    private $languageCreateCommand;
    /** @var PHPUnit_Framework_MockObject_MockObject */
    private $languageRepository;
    /** @var LanguageCreateCommandHandler */
    private $handler;

    protected function setUp() {
        $this->languageCreateCommand = new LanguageCreateCommand('CA-FR', 'PL', 'polski');
        $this->languageRepository = $this->createMock(LanguageRepository::class);
        $this->handler = new LanguageCreateCommandHandler($this->languageRepository);
    }

    public function testCreatingMetadata() {
        $this->languageRepository->expects($this->once())->method('save')->willReturnArgument(0);
        $language = $this->handler->handle($this->languageCreateCommand);
        $this->assertEquals('CA-FR', $language->getCode());
        $this->assertEquals('PL', $language->getFlag());
        $this->assertEquals('polski', $language->getName());
    }
}
