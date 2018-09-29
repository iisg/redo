<?php
namespace Repeka\Tests\Domain\UseCase\Metadata;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateCommand;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateCommandAdjuster;
use Repeka\Domain\Validation\Strippers\UnknownLanguageStripper;
use Repeka\Tests\Traits\StubsTrait;

class MetadataUpdateCommandAdjusterTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var  UnknownLanguageStripper */
    private $unknownLanguageStripper;

    protected function setUp() {
        $languageRepository = $this->createLanguageRepositoryMock(['PL']);
        $this->unknownLanguageStripper = new UnknownLanguageStripper($languageRepository);
    }

    public function testStrippingUnknownLanguagesOnPrepare() {
        $adjuster = new MetadataUpdateCommandAdjuster($this->unknownLanguageStripper, $this->createMock(MetadataRepository::class));
        $command = new MetadataUpdateCommand(
            $this->createMock(Metadata::class),
            ['PL' => 'TestLabel', 'EN' => 'TestLabel'],
            ['PL' => 'TestDescription', 'EN' => 'TestDescription'],
            ['PL' => 'TestPlaceholder', 'EN' => 'TestPlaceholder'],
            ['resourceKind' => [0]],
            '',
            false,
            false
        );
        /** @var MetadataUpdateCommand $preparedCommand */
        $preparedCommand = $adjuster->adjustCommand($command);
        $this->assertEquals(['PL' => 'TestLabel'], $preparedCommand->getNewLabel());
        $this->assertEquals(['PL' => 'TestDescription'], $preparedCommand->getNewDescription());
        $this->assertEquals(['PL' => 'TestPlaceholder'], $preparedCommand->getNewPlaceholder());
    }

    public function testReplacingEmptyGroupIdWithDefaultGroup() {
        $adjuster = new MetadataUpdateCommandAdjuster($this->unknownLanguageStripper, $this->createMock(MetadataRepository::class));
        $command = new MetadataUpdateCommand(
            $this->createMock(Metadata::class),
            ['PL' => 'label'],
            ['PL' => 'description'],
            ['PL' => 'placeholder'],
            ['resourceKind' => [0]],
            '',
            false,
            false
        );
        /** @var MetadataUpdateCommand $preparedCommand */
        $preparedCommand = $adjuster->adjustCommand($command);
        $this->assertEquals(Metadata::DEFAULT_GROUP, $preparedCommand->getNewGroupId());
    }
}
