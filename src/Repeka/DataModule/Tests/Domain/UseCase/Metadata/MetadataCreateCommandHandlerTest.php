<?php
namespace Repeka\DataModule\Tests\Domain\UseCase\Metadata;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\CoreModule\Domain\Validator\AnnotationBasedValidator;
use Repeka\DataModule\Domain\Repository\MetadataRepository;
use Repeka\DataModule\Domain\UseCase\Metadata\MetadataCreateCommand;
use Repeka\DataModule\Domain\UseCase\Metadata\MetadataCreateCommandHandler;

class MetadataCreateCommandHandlerTest extends \PHPUnit_Framework_TestCase {
    /** @var MetadataCreateCommand */
    private $metadataCreateCommand;
    /** @var PHPUnit_Framework_MockObject_MockObject */
    private $metadataRepository;
    /** @var MetadataCreateCommandHandler */
    private $handler;

    protected function setUp() {
        $this->metadataCreateCommand = new MetadataCreateCommand('nazwa', ['PL' => 'Labelka'], [], [], 'textarea');
        $this->metadataRepository = $this->createMock(MetadataRepository::class);
        $this->handler = new MetadataCreateCommandHandler($this->metadataRepository);
    }

    public function testCreatingMetadata() {
        $this->metadataRepository->expects($this->once())->method('save')->willReturnArgument(0);
        $metadata = $this->handler->handle($this->metadataCreateCommand);
        $this->assertEquals('nazwa', $metadata->getName());
        $this->assertEquals('Labelka', $metadata->getLabel()['PL']);
        $this->assertEmpty($metadata->getPlaceholder());
        $this->assertEmpty($metadata->getDescription());
        $this->assertEquals('textarea', $metadata->getControl());
    }
}
