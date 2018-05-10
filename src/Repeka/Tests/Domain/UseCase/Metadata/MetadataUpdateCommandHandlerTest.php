<?php
namespace Repeka\Tests\Domain\UseCase\Metadata;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateCommand;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateCommandHandler;

class MetadataUpdateCommandHandlerTest extends \PHPUnit_Framework_TestCase {
    /** @var Metadata */
    private $metadata;
    /** @var  MetadataRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $metadataRepository;
    /** @var MetadataUpdateCommandHandler */
    private $handler;

    protected function setUp() {
        $this->metadataRepository = $this->createMock(MetadataRepository::class);
        $this->handler = new MetadataUpdateCommandHandler($this->metadataRepository);
        $this->metadata = Metadata::create('books', MetadataControl::TEXT(), 'Prop', ['PL' => 'AA'], ['PL' => 'AA'], ['PL' => 'AA']);
        $this->metadataRepository->expects($this->atLeastOnce())->method('findOne')->willReturn($this->metadata);
        $this->metadataRepository->expects($this->atLeastOnce())->method('save')->with($this->metadata)->willReturnArgument(0);
    }

    public function testUpdating() {
        $dummy = new \stdClass();
        $command = MetadataUpdateCommand::fromArray(
            1,
            [
                'label' => ['PL' => 'new label'],
                'constraints' => [$dummy],
            ]
        );
        $updated = $this->handler->handle($command);
        $this->assertEquals(['PL' => 'new label'], $updated->getLabel());
        $this->assertEquals([$dummy], $updated->getConstraints());
    }

    public function testUpdatingWithChangedResourceClassDoesNotCauseChange() {
        $command = MetadataUpdateCommand::fromArray(
            1,
            [
                'resourceClass' => 'invalidResourceClass',
            ]
        );
        $updated = $this->handler->handle($command);
        $this->assertEquals('books', $updated->getResourceClass());
    }
}
