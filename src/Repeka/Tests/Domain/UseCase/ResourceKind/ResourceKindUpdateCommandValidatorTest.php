<?php
namespace Repeka\Tests\Domain\UseCase\ResourceKind;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Exception\InvalidCommandException;
use Repeka\Domain\Exception\RespectValidationFailedException;
use Repeka\Domain\UseCase\Metadata\MetadataCreateCommandValidator;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateCommandValidator;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindUpdateCommand;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindUpdateCommandValidator;
use Repeka\Domain\Utils\EntityUtils;
use Repeka\Domain\Validation\Rules\ChildResourceKindsAreOfSameResourceClassRule;
use Repeka\Domain\Validation\Rules\ContainsParentMetadataRule;
use Repeka\Domain\Validation\Rules\NotBlankInAllLanguagesRule;
use Repeka\Tests\Traits\StubsTrait;
use Respect\Validation\Validator;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ResourceKindUpdateCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceKindUpdateCommandValidator */
    private $validator;
    /** @var Metadata */
    private $relationshipMetadata;
    /** @var MetadataUpdateCommandValidator|\PHPUnit_Framework_MockObject_MockObject */
    private $metadataUpdateCommandValidator;
    /** @var ChildResourceKindsAreOfSameResourceClassRule|\PHPUnit_Framework_MockObject_MockObject */
    private $childResourceKindsAreOfSameResourceClassRule;
    /** @var NotBlankInAllLanguagesRule|\PHPUnit_Framework_MockObject_MockObject */
    private $notBlankInAllLanguagesRule;

    protected function setUp() {
        $metadataCreateCommandValidator = $this->createMock(MetadataCreateCommandValidator::class);
        $metadataCreateCommandValidator->method('getValidator')->willReturn(Validator::alwaysValid());
        $this->notBlankInAllLanguagesRule = $this->createMock(NotBlankInAllLanguagesRule::class);
        $this->childResourceKindsAreOfSameResourceClassRule = $this->createRuleMock(
            ChildResourceKindsAreOfSameResourceClassRule::class,
            true
        );
        $this->relationshipMetadata = Metadata::create(
            '',
            MetadataControl::RELATIONSHIP(),
            '',
            [],
            [],
            [],
            ['resourceKind' => [123]]
        );
        EntityUtils::forceSetId($this->relationshipMetadata, 2);
        $this->metadataUpdateCommandValidator = $this->createMock(MetadataUpdateCommandValidator::class);
        $this->validator = new ResourceKindUpdateCommandValidator(
            $this->notBlankInAllLanguagesRule,
            new ContainsParentMetadataRule(),
            $this->metadataUpdateCommandValidator,
            $this->childResourceKindsAreOfSameResourceClassRule
        );
    }

    public function testValid() {
        $this->metadataUpdateCommandValidator->method('getValidator')->willReturn(Validator::alwaysValid());
        $command = new ResourceKindUpdateCommand(
            $this->createMock(ResourceKind::class),
            ['PL' => 'Labelka'],
            [
                $this->createMetadataMock(SystemMetadata::PARENT),
                $this->createMetadataMock(),
                $this->relationshipMetadata,
            ],
            []
        );
        $this->validator->validate($command);
    }

    public function testInvalidWhenOnlyParentMetadata() {
        $this->expectException(InvalidCommandException::class);
        $this->metadataUpdateCommandValidator->method('getValidator')->willReturn(Validator::alwaysValid());
        $command = new ResourceKindUpdateCommand(
            $this->createMock(ResourceKind::class),
            ['PL' => 'Labelka'],
            [SystemMetadata::PARENT()->toMetadata()],
            []
        );
        $this->validator->validate($command);
    }

    public function testInvalidWhenNotOverrideMetadataValidator() {
        $this->expectException(RespectValidationFailedException::class);
        $this->metadataUpdateCommandValidator->method('getValidator')->willReturn(Validator::alwaysInvalid());
        $command = new ResourceKindUpdateCommand(
            $this->createMock(ResourceKind::class),
            ['PL' => 'Labelka'],
            [
                $this->createMetadataMock(SystemMetadata::PARENT),
                $this->createMetadataMock(),
            ],
            []
        );
        $this->validator->validate($command);
    }

    public function testValidIfWorkflowIsNull() {
        $rkWithWorkflow = $this->createResourceKindMock(1, 'books', [], $this->createMockEntity(ResourceWorkflow::class, 1));
        $command = new ResourceKindUpdateCommand(
            $rkWithWorkflow,
            ['PL' => 'Labelka'],
            [
                $this->createMetadataMock(SystemMetadata::PARENT),
                $this->createMetadataMock(),
                $this->relationshipMetadata,
            ]
        );
        $this->validator->validate($command);
    }

    public function testValidIfWorkflowTheSame() {
        $workflow = $this->createMockEntity(ResourceWorkflow::class, 1);
        $rkWithWorkflow = $this->createResourceKindMock(1, 'books', [], $workflow);
        $command = new ResourceKindUpdateCommand(
            $rkWithWorkflow,
            ['PL' => 'Labelka'],
            [
                $this->createMetadataMock(SystemMetadata::PARENT),
                $this->createMetadataMock(),
                $this->relationshipMetadata,
            ],
            $workflow
        );
        $this->validator->validate($command);
    }

    public function testInvalidIfTryingToChangeWorkflow() {
        $this->expectException(InvalidCommandException::class);
        $workflow = $this->createMockEntity(ResourceWorkflow::class, 1);
        $rkWithWorkflow = $this->createResourceKindMock(1, 'books', [], $workflow);
        $command = new ResourceKindUpdateCommand(
            $rkWithWorkflow,
            ['PL' => 'Labelka'],
            [
                $this->createMetadataMock(SystemMetadata::PARENT),
                $this->createMetadataMock(),
                $this->relationshipMetadata,
            ],
            [],
            $this->createMockEntity(ResourceWorkflow::class, 2)
        );
        $this->validator->validate($command);
    }
}
