<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommand;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommandValidator;
use Repeka\Domain\Validation\Rules\ValueSetMatchesResourceKindRule;
use Repeka\Tests\Traits\StubsTrait;

class ResourceCreateCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    private $resourceKind;
    /** @var ResourceCreateCommandValidator */
    private $validator;

    protected function setUp() {
        $this->resourceKind = $this->createMock(ResourceKind::class);
        $metadata1 = $this->createMock(Metadata::class);
        $metadata2 = $this->createMock(Metadata::class);
        $metadata1->expects($this->any())->method('getBaseId')->willReturn(1);
        $metadata2->expects($this->any())->method('getBaseId')->willReturn(2);
        $this->resourceKind->expects($this->any())->method('getId')->willReturn(1);
        $this->resourceKind->expects($this->any())->method('getMetadataList')->willReturn([$metadata1, $metadata2]);
        $this->validator = new ResourceCreateCommandValidator(new ValueSetMatchesResourceKindRule());
    }

    public function testValid() {
        $command = new ResourceCreateCommand($this->resourceKind, [2 => 'Some value']);
        $this->validator->validate($command);
    }

    public function testInvalidForNotInitializedResourceKind() {
        $command = new ResourceCreateCommand(new ResourceKind([]), [2 => 'Some value']);
        $this->assertFalse($this->validator->isValid($command));
    }

    public function testInvalidIfNonExistingMetadataId() {
        $command = new ResourceCreateCommand($this->resourceKind, [3 => 'Some value']);
        $this->assertFalse($this->validator->isValid($command));
    }

    public function testInvalidWhenNoContents() {
        $command = new ResourceCreateCommand($this->resourceKind, []);
        $this->assertFalse($this->validator->isValid($command));
    }
}
