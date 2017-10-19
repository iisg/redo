<?php
namespace Repeka\Tests\Domain\Factory;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Factory\MetadataFactory;
use Repeka\Domain\Factory\ResourceKindFactory;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindCreateCommand;
use Repeka\Tests\Traits\StubsTrait;

class ResourceKindFactoryTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var PHPUnit_Framework_MockObject_MockObject|MetadataRepository */
    private $metadataRepository;
    /** @var ResourceKindFactory */
    private $factory;
    /** @var ResourceKindCreateCommand */
    private $command;

    protected function setUp() {
        $this->metadataRepository = $this->createRepositoryStub(MetadataRepository::class, [
            $this->createMetadataMock(1),
            $this->createMetadataMock(2),
        ]);
        $this->command = new ResourceKindCreateCommand(['PL' => 'Labelka'], [
            $this->metadataArray(1, 'A', ['PL' => 'Label A']),
            $this->metadataArray(2, 'B', ['PL' => 'Label B']),
        ], 'books');
        $this->factory = new ResourceKindFactory(new MetadataFactory(), $this->metadataRepository);
    }

    public function testCreatingResourceKind() {
        $this->metadataRepository->expects($this->atLeastOnce())->method('findOne')
            ->willReturn(Metadata::create(MetadataControl::TEXT(), 'base', [], 'books'));
        $resourceKind = $this->factory->create($this->command);
        $this->assertEquals(['PL' => 'Labelka'], $resourceKind->getLabel());
        $this->assertCount(2, $resourceKind->getMetadataList());
        $firstMetadata = $resourceKind->getMetadataList()[0];
        $this->assertEquals('Label A', $firstMetadata->getLabel()['PL']);
    }

    public function testSavingMetadataOrdinalNumbers() {
        $this->metadataRepository->expects($this->atLeastOnce())->method('findOne')
            ->willReturn(Metadata::create(MetadataControl::TEXT(), 'base', [], 'books'));
        $resourceKind = $this->factory->create($this->command);
        $this->assertEquals(0, $resourceKind->getMetadataList()[0]->getOrdinalNumber());
        $this->assertEquals(1, $resourceKind->getMetadataList()[1]->getOrdinalNumber());
    }

    public function testCreatingResourceKindWithWorkflow() {
        $workflow = $this->createMock(ResourceWorkflow::class);
        $command = new ResourceKindCreateCommand(['PL' => 'Labelka'], [], 'books', $workflow);
        $resourceKind = $this->factory->create($command);
        $this->assertSame($workflow, $resourceKind->getWorkflow());
    }

    private function metadataArray(int $baseId, string $name, array $label) {
        return [
            'baseId' => $baseId,
            'name' => $name,
            'label' => $label,
            'description' => [],
            'placeholder' => [],
            'control' => 'text',
            'shownInBrief' => false,
            'resourceClass' => 'books',
        ];
    }
}
