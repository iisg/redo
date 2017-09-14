<?php
namespace Repeka\Tests\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\UseCase\Metadata\MetadataDeleteCommand;
use Repeka\Domain\UseCase\Metadata\MetadataDeleteCommandHandler;

class MetadataDeleteCommandHandlerTest extends \PHPUnit_Framework_TestCase {
    /** @var MetadataDeleteCommandHandler */
    private $handler;
    /** @var MetadataRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $metadataRepository;

    protected function setUp() {
        $this->metadataRepository = $this->createMock(MetadataRepository::class);
        $this->handler = new MetadataDeleteCommandHandler($this->metadataRepository);
    }

    public function testDeleting() {
        $metadata = $this->createMock(Metadata::class);
        $command = new MetadataDeleteCommand($metadata);
        $this->metadataRepository->expects($this->once())->method('delete')->with($metadata);
        $this->handler->handle($command);
    }
}
