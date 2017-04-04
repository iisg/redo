<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Exception\InvalidCommandException;
use Repeka\Domain\UseCase\Resource\ResourceUpdateContentsCommand;
use Repeka\Domain\UseCase\Resource\ResourceUpdateContentsCommandValidator;
use Repeka\Domain\Validation\Rules\ValueSetMatchesResourceKindRule;
use Repeka\Tests\Traits\StubsTrait;

class ResourceUpdateContentsCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceEntity|PHPUnit_Framework_MockObject_MockObject */
    private $resource;
    /** @var ResourceUpdateContentsCommandValidator */
    private $validator;

    protected function setUp() {
        $resourceKind = $this->createMock(ResourceKind::class);
        $metadata1 = $this->createMock(Metadata::class);
        $metadata2 = $this->createMock(Metadata::class);
        $metadata1->expects($this->any())->method('getBaseId')->willReturn(1);
        $metadata2->expects($this->any())->method('getBaseId')->willReturn(2);
        $resourceKind->expects($this->any())->method('getMetadataList')->willReturn([$metadata1, $metadata2]);
        $this->resource = $this->createMock(ResourceEntity::class);
        $this->resource->expects($this->any())->method('getKind')->willReturn($resourceKind);
        $this->resource->expects($this->any())->method('getId')->willReturn(1);
        $this->validator = new ResourceUpdateContentsCommandValidator(new ValueSetMatchesResourceKindRule());
    }

    public function testValid() {
        $command = new ResourceUpdateContentsCommand($this->resource, [2 => 'Some value']);
        $this->validator->validate($command);
    }

    public function testInvalidIfEmptyContents() {
        $this->expectException(InvalidCommandException::class);
        $command = new ResourceUpdateContentsCommand($this->resource, []);
        $this->validator->validate($command);
    }

    public function testInvalidIfIllegalContents() {
        $this->expectException(InvalidCommandException::class);
        $command = new ResourceUpdateContentsCommand($this->resource, [3 => 'Some value']);
        $this->validator->validate($command);
    }
}
