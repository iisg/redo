<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommand;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommandValidator;
use Repeka\Domain\Validation\MetadataConstraints\ResourceHasAllowedKindConstraint;
use Repeka\Domain\Validation\Rules\EntityExistsRule;
use Repeka\Domain\Validation\Rules\MetadataValuesSatisfyConstraintsRule;
use Repeka\Domain\Validation\Rules\ValueSetMatchesResourceKindRule;
use Repeka\Tests\Traits\StubsTrait;

class ResourceCreateCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    private $resourceKind;
    /** @var ResourceCreateCommandValidator */
    private $validator;

    protected function setUp() {
        $metadata = $this->createEntityLookupMap([
            $this->createMetadataMock(1),
            $this->createMetadataMock(2),
            $this->createMetadataMock(11, 1),
            $this->createMetadataMock(12, 2)
        ]);
        $resourceRepositoryStub = $this->createRepositoryStub(ResourceRepository::class, $metadata);
        $this->validator = new ResourceCreateCommandValidator(
            new ValueSetMatchesResourceKindRule(),
            new MetadataValuesSatisfyConstraintsRule($this->createMetadataConstraintProviderStub([
                'resourceKind' => new ResourceHasAllowedKindConstraint($resourceRepositoryStub, $this->createMock(EntityExistsRule::class))
            ]))
        );
        $this->resourceKind = $this->createMock(ResourceKind::class);
        $this->resourceKind->method('getId')->willReturn(1);
        $this->resourceKind->method('getMetadataList')->willReturn([$metadata[11], $metadata[12]]);
    }

    public function testValid() {
        $command = new ResourceCreateCommand($this->resourceKind, [2 => ['Some value']]);
        $this->validator->validate($command);
    }

    public function testInvalidForNotInitializedResourceKind() {
        $command = new ResourceCreateCommand(new ResourceKind([]), [2 => ['Some value']]);
        $this->assertFalse($this->validator->isValid($command));
    }

    public function testInvalidIfNonExistingMetadataId() {
        $command = new ResourceCreateCommand($this->resourceKind, [3 => ['Some value']]);
        $this->assertFalse($this->validator->isValid($command));
    }

    public function testInvalidWhenNoContents() {
        $command = new ResourceCreateCommand($this->resourceKind, []);
        $this->assertFalse($this->validator->isValid($command));
    }
}
