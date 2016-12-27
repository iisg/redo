<?php
namespace Domain\UseCase\Resource;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommand;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommandValidator;

class ResourceCreateCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    private $resourceKind;
    /** @var ResourceCreateCommandValidator */
    private $validator;

    protected function setUp() {
        $this->validator = new ResourceCreateCommandValidator();
        $this->resourceKind = $this->createMock(ResourceKind::class);
        $metadata1 = $this->createMock(Metadata::class);
        $metadata2 = $this->createMock(Metadata::class);
        $metadata1->expects($this->any())->method('getId')->willReturn(1);
        $metadata2->expects($this->any())->method('getId')->willReturn(2);
        $this->resourceKind->expects($this->any())->method('getId')->willReturn(1);
        $this->resourceKind->expects($this->any())->method('getMetadataList')->willReturn([$metadata1, $metadata2]);
    }

    public function testValid() {
        $command = new ResourceCreateCommand($this->resourceKind, [2 => 'Some value']);
        $this->validator->validate($command);
    }

    public function testInvalidForNotInitializedResourceKind() {
        $command = new ResourceCreateCommand(new ResourceKind([]), [2 => 'Some value']);
        $this->assertFalse($this->validator->isValid($command));
    }

    public function testInvalidiFNonExistingMetadataId() {
        $command = new ResourceCreateCommand($this->resourceKind, [3 => 'Some value']);
        $this->assertFalse($this->validator->isValid($command));
    }

    public function testInvalidWhenNoContents() {
        $command = new ResourceCreateCommand($this->resourceKind, []);
        $this->assertFalse($this->validator->isValid($command));
    }
}
