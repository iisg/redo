<?php
namespace Repeka\Tests\Domain\UseCase\ResourceKind;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\EntityUtils;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Exception\InvalidCommandException;
use Repeka\Domain\UseCase\Metadata\MetadataCreateCommandValidator;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindUpdateCommand;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindUpdateCommandValidator;
use Repeka\Domain\Validation\Rules\ChildResourceKindsAreOfSameResourceClassRule;
use Repeka\Domain\Validation\Rules\ContainsParentMetadataRule;
use Repeka\Domain\Validation\Rules\CorrectResourceDisplayStrategySyntaxRule;
use Repeka\Domain\Validation\Rules\NotBlankInAllLanguagesRule;
use Repeka\Domain\Validation\Rules\ResourceKindConstraintIsUserIfMetadataDeterminesAssigneeRule;
use Repeka\Tests\Traits\StubsTrait;
use Respect\Validation\Validator;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ResourceKindUpdateCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceKindConstraintIsUserIfMetadataDeterminesAssigneeRule|\PHPUnit_Framework_MockObject_MockObject */
    private $rkConstraintIsUser;
    /** @var ResourceKindUpdateCommandValidator */
    private $validator;
    /** @var CorrectResourceDisplayStrategySyntaxRule|\PHPUnit_Framework_MockObject_MockObject */
    private $correctResourceDisplayStrategySyntaxRule;
    /** @var Metadata */
    private $relationshipMetadata;

    protected function setUp() {
        $metadataCreateCommandValidator = $this->createMock(MetadataCreateCommandValidator::class);
        $metadataCreateCommandValidator->method('getValidator')->willReturn(Validator::alwaysValid());
        $notBlankInAllLanguagesRule = $this->createMock(NotBlankInAllLanguagesRule::class);
        $childResourceKindsAreOfSameResourceClassRule = $this->createRuleMock(ChildResourceKindsAreOfSameResourceClassRule::class, true);
        $this->correctResourceDisplayStrategySyntaxRule = $this->createMock(CorrectResourceDisplayStrategySyntaxRule::class);
        $this->rkConstraintIsUser = $this->createRuleWithFactoryMethodMock(
            ResourceKindConstraintIsUserIfMetadataDeterminesAssigneeRule::class,
            'forMetadata'
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
        $this->validator = new ResourceKindUpdateCommandValidator(
            $notBlankInAllLanguagesRule,
            $this->correctResourceDisplayStrategySyntaxRule,
            new ContainsParentMetadataRule(),
            $this->rkConstraintIsUser,
            $childResourceKindsAreOfSameResourceClassRule
        );
    }

    public function testValid() {
        $this->rkConstraintIsUser->method('validate')->willReturn(true);
        $command = new ResourceKindUpdateCommand($this->createMock(ResourceKind::class), ['PL' => 'Labelka'], [
            $this->createMetadataMock(SystemMetadata::PARENT),
            $this->createMetadataMock(),
            $this->relationshipMetadata,
        ], []);
        $this->validator->validate($command);
    }

    public function testInvalidIfNotResourceKindInstance() {
        $this->expectException(InvalidCommandException::class);
        $this->rkConstraintIsUser->method('validate')->willReturn(true);
        $command = new ResourceKindUpdateCommand(1, ['PL' => 'Labelka'], [
            $this->createMetadataMock(SystemMetadata::PARENT),
            $this->createMetadataMock(),
            $this->relationshipMetadata,
        ], []);
        $this->validator->validate($command);
    }

    public function testInvalidWhenRelationshipRequirementFails() {
        $this->expectException(InvalidCommandException::class);
        $this->rkConstraintIsUser->method('validate')->willReturn(false);
        $command = new ResourceKindUpdateCommand($this->createMock(ResourceKind::class), ['PL' => 'Labelka'], [
            $this->createMetadataMock(SystemMetadata::PARENT),
            $this->createMetadataMock(),
            $this->relationshipMetadata,
        ], []);
        $this->validator->validate($command);
    }

    public function testInvalidWhenOnlyParentMetadata() {
        $this->expectException(InvalidCommandException::class);
        $command = new ResourceKindUpdateCommand($this->createMock(ResourceKind::class), ['PL' => 'Labelka'], [
            SystemMetadata::PARENT()->toMetadata(),
        ], []);
        $this->validator->validate($command);
    }
}
