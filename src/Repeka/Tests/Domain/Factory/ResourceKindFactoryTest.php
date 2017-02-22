<?php
namespace Repeka\Tests\Domain\Factory;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Factory\MetadataFactory;
use Repeka\Domain\Factory\ResourceKindFactory;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindCreateCommand;

class ResourceKindFactoryTest extends \PHPUnit_Framework_TestCase {
    /** @var PHPUnit_Framework_MockObject_MockObject|MetadataRepository */
    private $metadataRepository;
    /** @var ResourceKindFactory */
    private $factory;
    /** @var ResourceKindCreateCommand */
    private $command;

    protected function setUp() {
        $this->metadataRepository = $this->createMock(MetadataRepository::class);
        $this->command = new ResourceKindCreateCommand(['PL' => 'Labelka'], [
            ['baseId' => 1, 'name' => 'A', 'label' => ['PL' => 'Label A'], 'description' => [], 'placeholder' => [], 'control' => 'text'],
            ['baseId' => 1, 'name' => 'B', 'label' => ['PL' => 'Label B'], 'description' => [], 'placeholder' => [], 'control' => 'text'],
        ]);
        $this->factory = new ResourceKindFactory(new MetadataFactory(), $this->metadataRepository);
    }

    public function testCreatingResourceKind() {
        $this->metadataRepository->expects($this->atLeastOnce())->method('findOne')->willReturn(Metadata::create('text', 'base', []));
        $resourceKind = $this->factory->create($this->command);
        $this->assertEquals(['PL' => 'Labelka'], $resourceKind->getLabel());
        $this->assertCount(2, $resourceKind->getMetadataList());
        $firstMetadata = $resourceKind->getMetadataList()[0];
        $this->assertEquals('Label A', $firstMetadata->getLabel()['PL']);
    }

    public function testSavingMetadataOrdinalNumbers() {
        $this->metadataRepository->expects($this->atLeastOnce())->method('findOne')->willReturn(Metadata::create('text', 'base', []));
        $resourceKind = $this->factory->create($this->command);
        $this->assertEquals(0, $resourceKind->getMetadataList()[0]->getOrdinalNumber());
        $this->assertEquals(1, $resourceKind->getMetadataList()[1]->getOrdinalNumber());
    }

    public function testCreatingResourceKindWithWorkflow() {
        $workflow = $this->createMock(ResourceWorkflow::class);
        $command = new ResourceKindCreateCommand(['PL' => 'Labelka'], [], $workflow);
        $resourceKind = $this->factory->create($command);
        $this->assertSame($workflow, $resourceKind->getWorkflow());
    }
}
