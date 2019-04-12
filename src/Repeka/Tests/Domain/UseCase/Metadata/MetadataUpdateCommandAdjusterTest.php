<?php
namespace Repeka\Tests\Domain\UseCase\Metadata;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateCommand;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateCommandAdjuster;
use Repeka\Domain\Validation\MetadataConstraintManager;
use Repeka\Domain\Validation\Strippers\UnknownLanguageStripper;
use Repeka\Domain\Validation\Strippers\UnknownMetadataGroupStripper;
use Repeka\Tests\Traits\StubsTrait;

class MetadataUpdateCommandAdjusterTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var  UnknownLanguageStripper */
    private $unknownLanguageStripper;
    private $metadataConstraintManager;
    /** @var MetadataUpdateCommandAdjuster */
    private $adjuster;

    /** @var MetadataConstraintManager|\PHPUnit_Framework_MockObject_MockObject */
    protected function setUp() {
        $languageRepository = $this->createLanguageRepositoryMock(['PL']);
        $this->unknownLanguageStripper = new UnknownLanguageStripper($languageRepository);
        $this->metadataConstraintManager = $this->createMock(MetadataConstraintManager::class);
        $unknownMetadataGroupStripper = new UnknownMetadataGroupStripper([]);
        $this->adjuster = new MetadataUpdateCommandAdjuster(
            $this->unknownLanguageStripper,
            $this->metadataConstraintManager,
            $this->createRepositoryStub(MetadataRepository::class, [$this->createMetadataMock()]),
            $unknownMetadataGroupStripper
        );
    }

    public function testStrippingUnknownLanguagesOnPrepare() {
        $command = new MetadataUpdateCommand(
            $this->createMetadataMock(),
            ['PL' => 'TestLabel', 'EN' => 'TestLabel'],
            ['PL' => 'TestDescription', 'EN' => 'TestDescription'],
            ['PL' => 'TestPlaceholder', 'EN' => 'TestPlaceholder'],
            ['resourceKind' => [0]],
            '',
            null,
            false,
            false
        );
        /** @var MetadataUpdateCommand $preparedCommand */
        $preparedCommand = $this->adjuster->adjustCommand($command);
        $this->assertEquals(['PL' => 'TestLabel'], $preparedCommand->getNewLabel());
        $this->assertEquals(['PL' => 'TestDescription'], $preparedCommand->getNewDescription());
        $this->assertEquals(['PL' => 'TestPlaceholder'], $preparedCommand->getNewPlaceholder());
    }

    public function testReplacingEmptyGroupIdWithDefaultGroup() {
        $command = new MetadataUpdateCommand(
            $this->createMetadataMock(),
            ['PL' => 'label'],
            ['PL' => 'description'],
            ['PL' => 'placeholder'],
            ['resourceKind' => [0]],
            '',
            null,
            false,
            false
        );
        /** @var MetadataUpdateCommand $preparedCommand */
        $preparedCommand = $this->adjuster->adjustCommand($command);
        $this->assertEquals(Metadata::DEFAULT_GROUP, $preparedCommand->getNewGroupId());
    }

    public function testClearingUnsupportedConstraints() {
        $command = new MetadataUpdateCommand(
            $this->createMetadataMock(),
            ['PL' => 'label'],
            ['PL' => 'description'],
            ['PL' => 'placeholder'],
            ['a' => 'a', 'b' => 'b'],
            '',
            null,
            false,
            false
        );
        $this->metadataConstraintManager->method('getSupportedConstraintNamesForControl')->willReturn(['b']);
        /** @var MetadataUpdateCommand $preparedCommand */
        $preparedCommand = $this->adjuster->adjustCommand($command);
        $this->assertEquals(['b' => 'b'], $preparedCommand->getNewConstraints());
    }
}
