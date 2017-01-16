<?php
namespace Repeka\Tests\Domain\UseCase\Language;

use Repeka\Domain\Entity\Language;
use Repeka\Domain\Repository\LanguageRepository;
use Repeka\Domain\UseCase\Language\LanguageUpdateCommand;
use Repeka\Domain\UseCase\Language\LanguageUpdateCommandHandler;

class LanguageUpdateCommandHandlerTest extends \PHPUnit_Framework_TestCase {
    /** @var Language */
    private $language;
    /** @var  LanguageRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $languageRepository;
    /** @var LanguageUpdateCommandHandler */
    private $handler;

    protected function setUp() {
        $this->languageRepository = $this->createMock(LanguageRepository::class);
        $this->handler = new LanguageUpdateCommandHandler($this->languageRepository);
        $this->language = new Language('PL', 'PL', 'polski');
        $this->languageRepository->expects($this->atLeastOnce())->method('findOne')->willReturn($this->language);
        $this->languageRepository->expects($this->atLeastOnce())->method('save')->with($this->language)->willReturnArgument(0);
    }

    public function testUpdating() {
        $command = LanguageUpdateCommand::fromArray('PL', ['flag' => 'GB', 'name' => 'polski']);
        $updated = $this->handler->handle($command);
        $this->assertEquals('GB', $updated->getFlag());
    }
}
