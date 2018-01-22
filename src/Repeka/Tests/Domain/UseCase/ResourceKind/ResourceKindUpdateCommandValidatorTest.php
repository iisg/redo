<?php
namespace Repeka\Tests\Domain\UseCase\ResourceKind;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Exception\InvalidCommandException;
use Repeka\Domain\Repository\LanguageRepository;
use Repeka\Domain\UseCase\Metadata\MetadataCreateCommandValidator;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindUpdateCommand;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindUpdateCommandValidator;
use Repeka\Domain\Validation\Rules\ContainsParentMetadataRule;
use Repeka\Domain\Validation\Rules\CorrectResourceDisplayStrategySyntaxRule;
use Repeka\Domain\Validation\Rules\NotBlankInAllLanguagesRule;
use Repeka\Domain\Validation\Rules\ResourceKindConstraintIsUserIfMetadataDeterminesAssigneeRule;
use Repeka\Domain\Validation\Strippers\UnknownLanguageStripper;
use Repeka\Tests\Traits\StubsTrait;
use Respect\Validation\Validator;

class ResourceKindUpdateCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceKindConstraintIsUserIfMetadataDeterminesAssigneeRule|\PHPUnit_Framework_MockObject_MockObject */
    private $rkConstraintIsUser;
    /** @var ResourceKindUpdateCommandValidator */
    private $validator;
    /** @var CorrectResourceDisplayStrategySyntaxRule|\PHPUnit_Framework_MockObject_MockObject */
    private $correctResourceDisplayStrategySyntaxRule;

    protected function setUp() {
        $languageRepository = $this->createMock(LanguageRepository::class);
        $languageRepository->method('getAvailableLanguageCodes')->willReturn(['PL']);
        $metadataCreateCommandValidator = $this->createMock(MetadataCreateCommandValidator::class);
        $metadataCreateCommandValidator->method('getValidator')->willReturn(Validator::alwaysValid());
        $notBlankInAllLanguagesRule = $this->createMock(NotBlankInAllLanguagesRule::class);
        $this->correctResourceDisplayStrategySyntaxRule = $this->createMock(CorrectResourceDisplayStrategySyntaxRule::class);
        $this->rkConstraintIsUser = $this->createRuleWithFactoryMethodMock(
            ResourceKindConstraintIsUserIfMetadataDeterminesAssigneeRule::class,
            'forMetadataId'
        );
        $languageRepository = $this->createLanguageRepositoryMock(['PL']);
        $this->validator = new ResourceKindUpdateCommandValidator(
            $notBlankInAllLanguagesRule,
            $this->rkConstraintIsUser,
            $this->correctResourceDisplayStrategySyntaxRule,
            new UnknownLanguageStripper($languageRepository),
            new ContainsParentMetadataRule()
        );
    }

    public function testValid() {
        $this->rkConstraintIsUser->expects($this->once())->method('validate')->willReturn(true);
        $command = new ResourceKindUpdateCommand(1, ['PL' => 'Labelka'], [
            [
                'baseId' => SystemMetadata::PARENT,
                'name' => 'A',
                'label' => [],
                'description' => [],
                'placeholder' => [],
                'control' => 'text'
            ],
            ['baseId' => 1, 'name' => 'A', 'label' => ['PL' => 'Label A'], 'description' => [], 'placeholder' => [], 'control' => 'text'],
            ['baseId' => 1, 'name' => 'B', 'label' => ['PL' => 'Label B'], 'description' => [], 'placeholder' => [],
                'control' => 'relationship', 'constraints' => ['resourceKind' => [123]]],
        ], []);
        $this->validator->validate($command);
    }

    public function testInvalidWhenRelationshipRequirementFails() {
        $this->expectException(InvalidCommandException::class);
        $this->rkConstraintIsUser->expects($this->once())->method('validate')->willReturn(false);
        $command = new ResourceKindUpdateCommand(1, ['PL' => 'Labelka'], [
            [
                'baseId' => SystemMetadata::PARENT,
                'name' => 'A', 'label' => [],
                'description' => [],
                'placeholder' => [],
                'control' => 'text'
            ],
            ['baseId' => 1, 'name' => 'A', 'label' => ['PL' => 'Label A'], 'description' => [], 'placeholder' => [], 'control' => 'text'],
            ['baseId' => 1, 'name' => 'B', 'label' => ['PL' => 'Label B'], 'description' => [], 'placeholder' => [],
                'control' => 'relationship', 'constraints' => ['resourceKind' => [123]]],
        ], []);
        $this->validator->validate($command);
    }

    public function testInvalidWhenOnlyParentMetadata() {
        $this->expectException(InvalidCommandException::class);
        $command = new ResourceKindUpdateCommand(1, ['PL' => 'Labelka'], [
            [
                'baseId' => SystemMetadata::PARENT,
                'name' => 'A',
                'label' => [],
                'description' => [],
                'placeholder' => [],
                'control' => 'text'
            ],
        ], []);
        $this->validator->validate($command);
    }

    public function testRemovesInvalidLanguagesOnPrepare() {
        $command = new ResourceKindUpdateCommand(1, ['PL' => 'Labelka', 'EN' => 'Labelka'], [], []);
        /** @var ResourceKindUpdateCommand $preparedCommand */
        $preparedCommand = $this->validator->prepareCommand($command);
        $this->assertEquals(1, $preparedCommand->getResourceKindId());
        $this->assertEquals(['PL' => 'Labelka'], $preparedCommand->getLabel());
    }
}
