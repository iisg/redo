<?php
namespace Repeka\Tests\Domain\UseCase\ResourceKind;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\Repository\ResourceWorkflowRepository;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindUpdateCommand;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindUpdateCommandAdjuster;
use Repeka\Domain\Validation\MetadataConstraintManager;
use Repeka\Domain\Validation\Strippers\UnknownLanguageStripper;
use Repeka\Tests\Traits\StubsTrait;

class ResourceKindUpdateCommandAdjusterTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceKindUpdateCommandAdjuster */
    private $adjuster;

    protected function setUp() {
        $languageRepository = $this->createLanguageRepositoryMock(['PL']);
        $resourceKindRespository = $this->createRepositoryStub(
            ResourceKindRepository::class,
            [
                1 => $this->createMock(ResourceKind::class),
            ]
        );
        $metadataRepository = $this->createRepositoryStub(
            MetadataRepository::class,
            [
                SystemMetadata::PARENT()->toMetadata(),
                SystemMetadata::REPRODUCTOR()->toMetadata(),
                SystemMetadata::RESOURCE_LABEL()->toMetadata(),
                SystemMetadata::VISIBILITY()->toMetadata(),
                SystemMetadata::TEASER_VISIBILITY()->toMetadata(),
            ]
        );
        $metadataConstraintManager = $this->createMock(MetadataConstraintManager::class);
        $this->adjuster = new ResourceKindUpdateCommandAdjuster(
            $metadataRepository,
            new UnknownLanguageStripper($languageRepository),
            $metadataConstraintManager,
            $resourceKindRespository,
            $this->createMock(ResourceWorkflowRepository::class)
        );
    }

    public function testRemovesInvalidLanguages() {
        $resourceKind = $this->createMock(ResourceKind::class);
        $command = new ResourceKindUpdateCommand($resourceKind, ['PL' => 'Labelka', 'EN' => 'Labelka'], [], []);
        /** @var ResourceKindUpdateCommand $preparedCommand */
        $preparedCommand = $this->adjuster->adjustCommand($command);
        $this->assertEquals($resourceKind, $preparedCommand->getResourceKind());
        $this->assertEquals(['PL' => 'Labelka'], $preparedCommand->getLabel());
    }

    public function testFetchesResourceKindIfIdGiven() {
        $command = new ResourceKindUpdateCommand(1, [], [], []);
        /** @var ResourceKindUpdateCommand $preparedCommand */
        $preparedCommand = $this->adjuster->adjustCommand($command);
        $this->assertInstanceOf(ResourceKind::class, $preparedCommand->getResourceKind());
    }

    public function testAcceptsStringIds() {
        $command = new ResourceKindUpdateCommand('1', [], [], []);
        /** @var ResourceKindUpdateCommand $preparedCommand */
        $preparedCommand = $this->adjuster->adjustCommand($command);
        $this->assertInstanceOf(ResourceKind::class, $preparedCommand->getResourceKind());
    }

    public function test404IfNotFoundId() {
        $this->expectException(EntityNotFoundException::class);
        $command = new ResourceKindUpdateCommand(2, [], [], []);
        $this->adjuster->adjustCommand($command);
    }

    public function testAssertionErrorIfWrongId() {
        $this->expectException(\InvalidArgumentException::class);
        $command = new ResourceKindUpdateCommand('ala', [], [], []);
        $this->adjuster->adjustCommand($command);
    }
}
