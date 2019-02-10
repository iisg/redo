<?php
namespace Repeka\Tests\Domain\UseCase\ResourceKind;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceWorkflowRepository;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindCreateCommand;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindCreateCommandAdjuster;
use Repeka\Domain\Utils\EntityUtils;
use Repeka\Domain\Validation\MetadataConstraintManager;
use Repeka\Domain\Validation\Strippers\UnknownLanguageStripper;
use Repeka\Tests\Traits\StubsTrait;

class ResourceKindCreateCommandAdjusterTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceKindCreateCommandAdjuster */
    private $adjuster;

    protected function setUp() {
        $languageRepository = $this->createLanguageRepositoryMock(['PL']);
        $realMetadata = Metadata::create('books', MetadataControl::TEXT(), 'name', ['PL' => 'A']);
        EntityUtils::forceSetId($realMetadata, 11);
        $metadataRepository = $this->createRepositoryStub(
            MetadataRepository::class,
            [
                SystemMetadata::PARENT()->toMetadata(),
                SystemMetadata::REPRODUCTOR()->toMetadata(),
                SystemMetadata::RESOURCE_LABEL()->toMetadata(),
                $this->createMetadataMock(55),
                $realMetadata,
            ]
        );
        $metadataConstraintManager = $this->createMock(MetadataConstraintManager::class);
        $metadataConstraintManager->method('getSupportedConstraintNamesForControl')->willReturn(['maxCount', 'resourceKind']);
        $workflowRepository = $this->createRepositoryStub(
            ResourceWorkflowRepository::class,
            [
                $this->createMockEntity(ResourceWorkflow::class, 1),
            ]
        );
        $this->adjuster = new ResourceKindCreateCommandAdjuster(
            $metadataRepository,
            new UnknownLanguageStripper($languageRepository),
            $metadataConstraintManager,
            $workflowRepository
        );
    }

    public function testRemovesInvalidLanguages() {
        $command = new ResourceKindCreateCommand('', ['PL' => 'Labelka', 'EN' => 'Labelka'], []);
        $adjustedCommand = $this->adjuster->adjustCommand($command);
        $this->assertEquals(['PL' => 'Labelka'], $adjustedCommand->getLabel());
    }

    public function testLeavesWorkflow() {
        $workflow = $this->createMock(ResourceWorkflow::class);
        $command = new ResourceKindCreateCommand('', [], [], $workflow);
        $adjustedCommand = $this->adjuster->adjustCommand($command);
        $this->assertEquals($workflow, $adjustedCommand->getWorkflow());
    }

    public function testTransformsWorkflowIdToWorkflow() {
        $command = new ResourceKindCreateCommand('', [], [], 1);
        $adjustedCommand = $this->adjuster->adjustCommand($command);
        $this->assertInstanceOf(ResourceWorkflow::class, $adjustedCommand->getWorkflow());
    }

    public function testOneMetadataInstanceInArray() {
        $metadata = $this->createMetadataMock();
        $command = new ResourceKindCreateCommand('', [], [$metadata]);
        $adjustedCommand = $this->adjuster->adjustCommand($command);
        $this->assertContains($metadata, $adjustedCommand->getMetadataList());
    }

    public function testMaintainingMetadataOrder() {
        $metadata1 = $this->createMetadataMock();
        $metadata2 = $this->createMetadataMock(2);
        $command = new ResourceKindCreateCommand('', [], [$metadata1, $metadata2]);
        $adjustedCommand = $this->adjuster->adjustCommand($command);
        $this->assertEquals($metadata1, $adjustedCommand->getMetadataList()[0]);
        $this->assertEquals($metadata2, $adjustedCommand->getMetadataList()[1]);
    }

    public function testDeduplicatesMetadata() {
        $metadata = $this->createMetadataMock();
        $command = new ResourceKindCreateCommand('', [], [$metadata, $metadata, $metadata]);
        $adjustedCommand = $this->adjuster->adjustCommand($command);
        $this->assertCount(4, $adjustedCommand->getMetadataList());
    }

    public function testAddingParentMetadataIfMissing() {
        $metadata = $this->createMetadataMock();
        $command = new ResourceKindCreateCommand('', [], [$metadata]);
        $adjustedCommand = $this->adjuster->adjustCommand($command);
        $this->assertCount(4, $adjustedCommand->getMetadataList());
        $ids = EntityUtils::mapToIds($adjustedCommand->getMetadataList());
        $this->assertContains(SystemMetadata::PARENT, $ids);
    }

    public function testAddingReproductorMetadataIfMissing() {
        $metadata = $this->createMetadataMock();
        $command = new ResourceKindCreateCommand('', [], [$metadata]);
        $adjustedCommand = $this->adjuster->adjustCommand($command);
        $this->assertCount(4, $adjustedCommand->getMetadataList());
        $ids = EntityUtils::mapToIds($adjustedCommand->getMetadataList());
        $this->assertContains(SystemMetadata::REPRODUCTOR, $ids);
    }

    public function testCannotOverwriteReproductorMetadata() {
        $metadata = $this->createMetadataMock();
        $command = new ResourceKindCreateCommand(
            '',
            [],
            [$metadata, ['id' => SystemMetadata::REPRODUCTOR, 'constraints' => ['resourceKind' => [1, 2]]]]
        );
        $adjustedCommand = $this->adjuster->adjustCommand($command);
        $reproductorMetadata = EntityUtils::filterByIds([SystemMetadata::REPRODUCTOR], $adjustedCommand->getMetadataList())[0];
        $this->assertEmpty($reproductorMetadata->getConstraints());
    }

    public function testAddingResourceLabelMetadataIfMissing() {
        $metadata = $this->createMetadataMock();
        $command = new ResourceKindCreateCommand('', [], [$metadata]);
        $adjustedCommand = $this->adjuster->adjustCommand($command);
        $this->assertCount(4, $adjustedCommand->getMetadataList());
        $ids = EntityUtils::mapToIds($adjustedCommand->getMetadataList());
        $this->assertContains(SystemMetadata::RESOURCE_LABEL, $ids);
    }

    public function testNotAddingParentMetadataIfExplicitlyAdded() {
        $metadata = $this->createMetadataMock();
        $command = new ResourceKindCreateCommand('', [], [SystemMetadata::PARENT()->toMetadata(), $metadata]);
        $adjustedCommand = $this->adjuster->adjustCommand($command);
        $this->assertCount(4, $adjustedCommand->getMetadataList());
        $ids = EntityUtils::mapToIds($adjustedCommand->getMetadataList());
        $this->assertEquals([SystemMetadata::PARENT, 1, SystemMetadata::REPRODUCTOR, SystemMetadata::RESOURCE_LABEL], $ids);
    }

    public function testAddingMetadataFromArray() {
        $command = new ResourceKindCreateCommand('', [], [['id' => 55]]);
        $adjustedCommand = $this->adjuster->adjustCommand($command);
        $this->assertCount(4, $adjustedCommand->getMetadataList());
        $this->assertEquals(55, $adjustedCommand->getMetadataList()[0]->getId());
    }

    public function testMixingRealAndArrayMetadata() {
        $command = new ResourceKindCreateCommand('', [], [['id' => 55], $this->createMetadataMock()]);
        $adjustedCommand = $this->adjuster->adjustCommand($command);
        $this->assertCount(5, $adjustedCommand->getMetadataList());
    }

    public function test404IfNonExistingMetadata() {
        $this->expectException(EntityNotFoundException::class);
        $command = new ResourceKindCreateCommand('', [], [['id' => 56]]);
        $this->adjuster->adjustCommand($command);
    }

    public function testAddingMetadataWithOverrides() {
        $command = new ResourceKindCreateCommand('', [], [['id' => 11, 'label' => ['PL' => 'Nadpisana']]]);
        $adjustedCommand = $this->adjuster->adjustCommand($command);
        $this->assertCount(4, $adjustedCommand->getMetadataList());
        $this->assertEquals(11, $adjustedCommand->getMetadataList()[0]->getId());
        $this->assertEquals('Nadpisana', $adjustedCommand->getMetadataList()[0]->getLabel()['PL']);
    }

    public function testAddingMetadataWithOverriddenConstraints() {
        $command = new ResourceKindCreateCommand(
            '',
            [],
            [['id' => 11, 'label' => ['PL' => 'Nadpisana'], 'constraints' => ['maxCount' => 1]]]
        );
        $adjustedCommand = $this->adjuster->adjustCommand($command);
        $this->assertCount(4, $adjustedCommand->getMetadataList());
        $this->assertEquals(11, $adjustedCommand->getMetadataList()[0]->getId());
        $this->assertEquals('Nadpisana', $adjustedCommand->getMetadataList()[0]->getLabel()['PL']);
        $this->assertEquals(['maxCount' => 1], $adjustedCommand->getMetadataList()[0]->getConstraints());
    }

    public function testStrippingOutUnsupportedConstraints() {
        $command = new ResourceKindCreateCommand(
            '',
            [],
            [['id' => 11, 'label' => ['PL' => 'Nadpisana'], 'constraints' => ['maxCount' => 1, 'unicornCount' => 43]]]
        );
        $adjustedCommand = $this->adjuster->adjustCommand($command);
        $this->assertCount(4, $adjustedCommand->getMetadataList());
        $this->assertEquals(11, $adjustedCommand->getMetadataList()[0]->getId());
        $this->assertEquals('Nadpisana', $adjustedCommand->getMetadataList()[0]->getLabel()['PL']);
        $this->assertEquals(['maxCount' => 1], $adjustedCommand->getMetadataList()[0]->getConstraints());
    }
}
