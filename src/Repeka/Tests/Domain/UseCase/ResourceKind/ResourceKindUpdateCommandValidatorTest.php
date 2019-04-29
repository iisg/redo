<?php
namespace Repeka\Tests\Domain\UseCase\ResourceKind;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Exception\InvalidCommandException;
use Repeka\Domain\Exception\RespectValidationFailedException;
use Repeka\Domain\Repository\LanguageRepository;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateCommandAdjuster;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateCommandValidator;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindCreateCommandValidator;
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

    /** @var ResourceKindCreateCommandValidator */
    private $validator;
    /** @var ResourceKind|\PHPUnit_Framework_MockObject_MockObject */
    private $resourceKind;
    /** @var Metadata */
    private $relationshipMetadata;
    /** @var  LanguageRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $languageRepository;
    /** @var MetadataUpdateCommandValidator|\PHPUnit_Framework_MockObject_MockObject */
    private $metadataUpdateCommandValidator;
    /** @var ChildResourceKindsAreOfSameResourceClassRule|\PHPUnit_Framework_MockObject_MockObject */
    private $childResourceKindsAreOfSameResourceClassRule;
    /** @var NotBlankInAllLanguagesRule|\PHPUnit_Framework_MockObject_MockObject */
    private $notBlankInAllLanguagesRule;
    /** @var ResourceKindRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $resourceKindRepository;

    protected function setUp() {
        $this->languageRepository = $this->createMock(LanguageRepository::class);
        $this->languageRepository->expects($this->atLeastOnce())->method('getAvailableLanguageCodes')->willReturn(['PL']);
        $this->resourceKindRepository = $this->createMock(ResourceKindRepository::class);
        $this->resourceKindRepository->method('countByQuery')->willReturn(0);
        $metadataUpdateCommandAdjuster = $this->createMock(MetadataUpdateCommandAdjuster::class);
        $metadataUpdateCommandAdjuster->method('adjustCommand')->willReturnArgument(0);
        $this->notBlankInAllLanguagesRule = $this->createMock(NotBlankInAllLanguagesRule::class);
        $this->childResourceKindsAreOfSameResourceClassRule = new ChildResourceKindsAreOfSameResourceClassRule(
            $this->resourceKindRepository
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
        $this->resourceKind = $this->createMock(ResourceKind::class);
        $this->resourceKind->method('getName')->willReturn('testName');
        $this->validator = new ResourceKindUpdateCommandValidator(
            new NotBlankInAllLanguagesRule($this->languageRepository),
            new ContainsParentMetadataRule(),
            $metadataUpdateCommandAdjuster,
            $this->metadataUpdateCommandValidator,
            new ChildResourceKindsAreOfSameResourceClassRule($this->resourceKindRepository)
        );
    }

    public function testValid() {
        $this->metadataUpdateCommandValidator->method('getValidator')->willReturn(Validator::alwaysValid());
        $command = new ResourceKindUpdateCommand(
            $this->resourceKind,
            ['PL' => 'Labelka'],
            [
                $this->createMetadataMock(SystemMetadata::PARENT),
                $this->createMetadataMock(),
                $this->relationshipMetadata,
            ],
            false,
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
            false,
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
            false,
            []
        );
        $this->validator->validate($command);
    }

    public function testValidIfWorkflowIsNull() {
        $rkWithWorkflow = $this->createResourceKindMock(1, 'books', [], $this->createMockEntity(ResourceWorkflow::class, 1), 'test');
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
        $rkWithWorkflow = $this->createResourceKindMock(1, 'books', [], $workflow, 'test');
        $command = new ResourceKindUpdateCommand(
            $rkWithWorkflow,
            ['PL' => 'Labelka'],
            [
                $this->createMetadataMock(SystemMetadata::PARENT),
                $this->createMetadataMock(),
                $this->relationshipMetadata,
            ],
            false,
            $workflow
        );
        $this->validator->validate($command);
    }

    public function testInvalidIfTryingToChangeWorkflow() {
        $this->expectException(InvalidCommandException::class);
        $workflow = $this->createMockEntity(ResourceWorkflow::class, 1);
        $rkWithWorkflow = $this->createResourceKindMock(1, 'books', [], $workflow, 'test');
        $command = new ResourceKindUpdateCommand(
            $rkWithWorkflow,
            ['PL' => 'Labelka'],
            [
                $this->createMetadataMock(SystemMetadata::PARENT),
                $this->createMetadataMock(),
                $this->relationshipMetadata,
            ],
            false,
            $this->createMockEntity(ResourceWorkflow::class, 2)
        );
        $this->validator->validate($command);
    }

    public function testFailWhenNoLabel() {
        $this->metadataUpdateCommandValidator->method('getValidator')->willReturn(Validator::alwaysValid());
        $this->expectException(InvalidCommandException::class);
        $rkWithoutWorkflow = $this->createResourceKindMock(1, 'books', [], null, 'test');
        $command = new ResourceKindUpdateCommand(
            $rkWithoutWorkflow,
            [],
            [
                $this->createMetadataMock(SystemMetadata::PARENT),
                $this->createMetadataMock(),
            ]
        );
        $this->validator->validate($command);
    }

    public function testFailWhenNoMetadata() {
        $this->expectException(InvalidCommandException::class);
        $command = new ResourceKindUpdateCommand($this->createMock(ResourceKind::class), ['PL' => 'Labelka'], []);
        $this->validator->validate($command);
    }

    public function testFailWhenOnlyParentMetadata() {
        $this->metadataUpdateCommandValidator->method('getValidator')->willReturn(Validator::alwaysValid());
        $rkWithoutWorkflow = $this->createResourceKindMock(1, 'books', [], null, 'test');
        $this->expectException(InvalidCommandException::class);
        $command = new ResourceKindUpdateCommand(
            $rkWithoutWorkflow,
            ['PL' => 'Labelka'],
            [
                $this->createMetadataMock(SystemMetadata::PARENT),
                $this->createMetadataMock(SystemMetadata::PARENT),
            ]
        );
        $this->validator->validate($command);
    }

    public function testFailWhenNoParentMetadata() {
        $this->metadataUpdateCommandValidator->method('getValidator')->willReturn(Validator::alwaysValid());
        $this->expectException(InvalidCommandException::class);
        $command = new ResourceKindUpdateCommand(
            $this->createMock(ResourceKind::class),
            ['PL' => 'Labelka'],
            [
                $this->createMetadataMock(),
                $this->createMetadataMock(),
            ]
        );
        $this->validator->validate($command);
    }

    public function testFailWhenDifferentResourceClassesOfMetadata() {
        $this->metadataUpdateCommandValidator->method('getValidator')->willReturn(Validator::alwaysValid());
        $this->expectException(InvalidCommandException::class);
        $command = new ResourceKindUpdateCommand(
            $this->createMock(ResourceKind::class),
            ['PL' => 'Labelka'],
            [
                $this->createMetadataMock(SystemMetadata::PARENT),
                $this->createMetadataMock(1),
                $this->createMetadataMock(1, 1, null, [], 'unicorns'),
            ]
        );
        $this->validator->validate($command);
    }

    public function testIgnoringSystemMetadataResourceClass() {
        $this->metadataUpdateCommandValidator->method('getValidator')->willReturn(Validator::alwaysValid());
        $command = new ResourceKindUpdateCommand(
            $this->createMock(ResourceKind::class),
            ['PL' => 'Labelka'],
            [
                SystemMetadata::PARENT()->toMetadata(),
                SystemMetadata::REPRODUCTOR()->toMetadata(),
                $this->createMetadataMock(),
            ]
        );
        $this->validator->validate($command);
    }

    public function testFailsWhenChildrenHaveDifferentResourceClasses() {
        $this->metadataUpdateCommandValidator->method('getValidator')->willReturn(Validator::alwaysValid());
        $this->expectException(InvalidCommandException::class);
        $command = new ResourceKindUpdateCommand(
            $this->createMock(ResourceKind::class),
            ['PL' => 'Labelka'],
            [
                $this->createMetadataMock(SystemMetadata::PARENT, null, null, ['resourceKind' => [1, 2]]),
                $this->createMetadataMock(0, null, null, [], 'books'),
            ]
        );
        $this->validator->validate($command);
    }

    public function testFailsWhenNotOverrideMetadataValidator() {
        $this->metadataUpdateCommandValidator->method('getValidator')->willReturn(Validator::alwaysInvalid());
        $this->expectException(RespectValidationFailedException::class);
        $command = new ResourceKindUpdateCommand(
            $this->createMock(ResourceKind::class),
            ['PL' => 'Labelka'],
            [
                $this->createMetadataMock(SystemMetadata::PARENT),
                $this->createMetadataMock(1),
            ]
        );
        $this->validator->validate($command);
    }
}
