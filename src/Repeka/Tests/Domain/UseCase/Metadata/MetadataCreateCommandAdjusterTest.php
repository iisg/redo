<?php
namespace Repeka\Tests\Domain\UseCase\Metadata;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\UseCase\Metadata\MetadataCreateCommand;
use Repeka\Domain\UseCase\Metadata\MetadataCreateCommandAdjuster;
use Repeka\Domain\Validation\MetadataConstraintManager;
use Repeka\Domain\Validation\Strippers\UnknownLanguageStripper;
use Repeka\Domain\Validation\Strippers\UnknownMetadataGroupStripper;
use Repeka\Tests\Traits\StubsTrait;

class MetadataCreateCommandAdjusterTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var  UnknownLanguageStripper */
    private $unknownLanguageStripper;
    /** @var MetadataConstraintManager|\PHPUnit_Framework_MockObject_MockObject */
    private $metadataConstraintManager;
    private $adjuster;

    protected function setUp() {
        $languageRepository = $this->createLanguageRepositoryMock(['PL']);
        $this->unknownLanguageStripper = new UnknownLanguageStripper($languageRepository);
        $this->metadataConstraintManager = $this->createMock(MetadataConstraintManager::class);
        $unknownMetadataGroupStripper = new UnknownMetadataGroupStripper([]);
        $this->adjuster = new MetadataCreateCommandAdjuster(
            $this->unknownLanguageStripper,
            $this->metadataConstraintManager,
            $unknownMetadataGroupStripper
        );
    }

    public function testStrippingUnknownLanguagesOnPrepare() {
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
        $preparedCommand = $this->adjuster->adjustCommand($command);
        $this->assertEquals(['PL' => 'ok label'], $preparedCommand->getLabel());
        $this->assertEquals(['PL' => 'ok description'], $preparedCommand->getDescription());
        $this->assertEquals(['PL' => 'ok placeholder'], $preparedCommand->getPlaceholder());
    }

    public function testReplacingEmptyGroupIdWithDefaultGroup() {
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
        $preparedCommand = $this->adjuster->adjustCommand($command);
        $this->assertEquals(Metadata::DEFAULT_GROUP, $preparedCommand->getGroupId());
    }

    public function testClearingUnsupportedConstraints() {
        $command = new MetadataCreateCommand(
            'name',
            ['PL' => 'label'],
            ['PL' => 'description'],
            ['PL' => 'placeholder'],
            'text',
            'books',
            ['a' => 'a', 'b' => 'b'],
            ''
        );
        $this->metadataConstraintManager->method('getSupportedConstraintNamesForControl')->willReturn(['a']);
        /** @var MetadataCreateCommand $preparedCommand */
        $preparedCommand = $this->adjuster->adjustCommand($command);
        $this->assertEquals(['a' => 'a'], $preparedCommand->getConstraints());
    }
}
