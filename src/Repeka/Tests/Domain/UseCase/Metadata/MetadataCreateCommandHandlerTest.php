<?php
namespace Repeka\Tests\Domain\UseCase\Metadata;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\CoreModule\Domain\Validator\AnnotationBasedValidator;
use Repeka\Domain\Factory\MetadataFactory;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\UseCase\Metadata\MetadataCreateCommand;
use Repeka\Domain\UseCase\Metadata\MetadataCreateCommandHandler;

class MetadataCreateCommandHandlerTest extends \PHPUnit_Framework_TestCase {
    /** @var MetadataCreateCommand */
    private $metadataCreateCommand;
    /** @var PHPUnit_Framework_MockObject_MockObject|MetadataRepository */
    private $metadataRepository;
    /** @var MetadataCreateCommandHandler */
    private $handler;

    protected function setUp() {
        $this->metadataCreateCommand = new MetadataCreateCommand('nazwa', ['PL' => 'Labelka'], [], [], 'textarea', 'books');
        $this->metadataRepository = $this->createMock(MetadataRepository::class);
        $this->handler = new MetadataCreateCommandHandler(new MetadataFactory(), $this->metadataRepository);
    }

    public function testCreatingMetadata() {
        $this->metadataRepository->expects($this->once())->method('save')->willReturnArgument(0);
        $metadata = $this->handler->handle($this->metadataCreateCommand);
        $this->assertEquals('nazwa', $metadata->getName());
        $this->assertEquals('Labelka', $metadata->getLabel()['PL']);
        $this->assertEquals('books', $metadata->getResourceClass());
        $this->assertEmpty($metadata->getPlaceholder());
        $this->assertEmpty($metadata->getDescription());
        $this->assertEquals('textarea', $metadata->getControl());
    }
}
