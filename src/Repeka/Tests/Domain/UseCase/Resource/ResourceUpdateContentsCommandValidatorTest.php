<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Exception\InvalidCommandException;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Resource\ResourceUpdateContentsCommand;
use Repeka\Domain\UseCase\Resource\ResourceUpdateContentsCommandValidator;
use Repeka\Domain\Validation\MetadataConstraints\ResourceHasAllowedKindConstraint;
use Repeka\Domain\Validation\Rules\EntityExistsRule;
use Repeka\Domain\Validation\Rules\MetadataValuesSatisfyConstraintsRule;
use Repeka\Domain\Validation\Rules\ValueSetMatchesResourceKindRule;
use Repeka\Tests\Traits\StubsTrait;

class ResourceUpdateContentsCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceEntity|PHPUnit_Framework_MockObject_MockObject */
    private $resource;
    /** @var ResourceUpdateContentsCommandValidator */
    private $validator;

    protected function setUp() {
        $metadata = $this->createEntityLookupMap([
            $this->createMetadataMock(1),
            $this->createMetadataMock(2),
            $this->createMetadataMock(11, 1),
            $this->createMetadataMock(12, 2)
        ]);
        $resourceKind = $this->createMock(ResourceKind::class);
        $resourceKind->expects($this->any())->method('getMetadataList')->willReturn([$metadata[11], $metadata[12]]);
        $this->resource = $this->createMock(ResourceEntity::class);
        $this->resource->expects($this->any())->method('getKind')->willReturn($resourceKind);
        $this->resource->expects($this->any())->method('getId')->willReturn(1);
        $resourceRepository = $this->createRepositoryStub(ResourceRepository::class, $metadata);
        $this->validator = new ResourceUpdateContentsCommandValidator(
            new ValueSetMatchesResourceKindRule(),
            new MetadataValuesSatisfyConstraintsRule($this->createMetadataConstraintProviderStub([
                'resourceKind' => new ResourceHasAllowedKindConstraint($resourceRepository, $this->createMock(EntityExistsRule::class))
            ]))
        );
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
