<?php
namespace Repeka\Tests\Domain\UseCase\Metadata;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\UseCase\Metadata\MetadataCreateCommand;
use Repeka\Domain\UseCase\Metadata\MetadataCreateCommandAdjuster;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateCommand;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateCommandAdjuster;
use Repeka\Domain\Validation\Strippers\UnknownLanguageStripper;
use Repeka\Tests\Traits\StubsTrait;

class MetadataCreateCommandAdjusterTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var  UnknownLanguageStripper */
    private $unknownLanguageStripper;

    protected function setUp() {
        $languageRepository = $this->createLanguageRepositoryMock(['PL']);
        $this->unknownLanguageStripper = new UnknownLanguageStripper($languageRepository);
    }

    public function testStrippingUnknownLanguagesOnPrepare() {
        $adjuster = new MetadataCreateCommandAdjuster($this->unknownLanguageStripper);
        $command = new MetadataCreateCommand(
            'name',
            ['PL' => 'ok label', 'EN' => 'unknown language label'],
            ['PL' => 'ok description', 'EN' => 'unknown language description'],
            ['PL' => 'ok placeholder', 'EN' => 'unknown language placeholder'],
            'text',
            'books',
            []
        );
        /** @var MetadataCreateCommand $preparedCommand */
        $preparedCommand = $adjuster->adjustCommand($command);
        $this->assertEquals(['PL' => 'ok label'], $preparedCommand->getLabel());
        $this->assertEquals(['PL' => 'ok description'], $preparedCommand->getDescription());
        $this->assertEquals(['PL' => 'ok placeholder'], $preparedCommand->getPlaceholder());
    }

    public function testReplacingEmptyGroupIdWithDefaultGroup() {
        $adjuster = new MetadataCreateCommandAdjuster($this->unknownLanguageStripper);
        $command = new MetadataCreateCommand(
            'name',
            ['PL' => 'label'],
            ['PL' => 'description'],
            ['PL' => 'placeholder'],
            'text',
            'books',
            [],
            ''
        );
        /** @var MetadataCreateCommand $preparedCommand */
        $preparedCommand = $adjuster->adjustCommand($command);
        $this->assertEquals(Metadata::DEFAULT_GROUP, $preparedCommand->getGroupId());
    }
}
