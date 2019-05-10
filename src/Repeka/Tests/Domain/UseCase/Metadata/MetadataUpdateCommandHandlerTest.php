<?php
namespace Repeka\Tests\Domain\UseCase\Metadata;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateCommand;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateCommandHandler;

class MetadataUpdateCommandHandlerTest extends \PHPUnit_Framework_TestCase {
    /** @var Metadata|\PHPUnit_Framework_MockObject_MockObject */
    private $metadata;
    /** @var  MetadataRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $metadataRepository;
    /** @var MetadataUpdateCommandHandler */
    private $handler;

    protected function setUp() {
        $this->metadataRepository = $this->createMock(MetadataRepository::class);
        $this->handler = new MetadataUpdateCommandHandler($this->metadataRepository);
        $this->metadata = Metadata::create('books', MetadataControl::TEXT(), 'Prop', ['PL' => 'AA'], ['PL' => 'AA'], ['PL' => 'AA']);
        $this->metadataRepository->expects($this->atLeastOnce())->method('save')->with($this->metadata)->willReturnArgument(0);
    }

    public function testUpdating() {
        $dummy = new \stdClass();
        $command = MetadataUpdateCommand::fromArray(
            $this->metadata,
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
            $this->metadata,
            [
                'resourceClass' => 'invalidResourceClass',
            ]
        );
        $updated = $this->handler->handle($command);
        $this->assertEquals('books', $updated->getResourceClass());
    }

    public function testUsingCurrentMetadataValuesWhenMissing() {
        $dummy = new \stdClass();
        $this->metadata->update(['LABEL'], ['PLACEHOLDER'], ['DESCRIPTION'], [], 'AAA', null, true, false);
        $command = MetadataUpdateCommand::fromArray($this->metadata, ['constraints' => [$dummy]]);
        $updated = $this->handler->handle($command);
        $this->assertEquals(['LABEL'], $updated->getLabel());
        $this->assertEquals(['PLACEHOLDER'], $updated->getPlaceholder());
        $this->assertEquals(['DESCRIPTION'], $updated->getDescription());
        $this->assertEquals([$dummy], $updated->getConstraints());
        $this->assertEquals('AAA', $updated->getGroupId());
        $this->assertTrue($updated->isShownInBrief());
        $this->assertFalse($updated->isCopiedToChildResource());
    }
}
