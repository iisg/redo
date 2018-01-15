<?php
namespace Repeka\Tests\Domain\UseCase\Metadata;

use Repeka\Domain\Exception\InvalidCommandException;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateCommand;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateCommandValidator;
use Repeka\Domain\Validation\Rules\ConstraintArgumentsAreValidRule;
use Repeka\Domain\Validation\Rules\ConstraintSetMatchesControlRule;
use Repeka\Domain\Validation\Rules\ContainsOnlyAvailableLanguagesRule;
use Repeka\Domain\Validation\Rules\ResourceKindConstraintIsUserIfMetadataDeterminesAssigneeRule;
use Repeka\Domain\Validation\Strippers\UnknownLanguageStripper;
use Repeka\Tests\Traits\StubsTrait;

class MetadataUpdateCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ContainsOnlyAvailableLanguagesRule|\PHPUnit_Framework_MockObject_MockObject */
    private $languagesRule;
    /** @var ConstraintSetMatchesControlRule|\PHPUnit_Framework_MockObject_MockObject */
    private $constraintSetRule;
    /** @var ConstraintArgumentsAreValidRule|\PHPUnit_Framework_MockObject_MockObject */
    private $constraintArgumentsRule;
    /** @var ResourceKindConstraintIsUserIfMetadataDeterminesAssigneeRule|\PHPUnit_Framework_MockObject_MockObject */
    private $rkConstraintRule;
    /** @var  UnknownLanguageStripper */
    private $unknownLanguageStripper;

    /** @var MetadataUpdateCommand */
    private $command;

    private function constraintSetMatchesControlRule(bool $result): ConstraintSetMatchesControlRule {
        /** @var ConstraintSetMatchesControlRule|\PHPUnit_Framework_MockObject_MockObject $rule */
        $rule = $this->createRuleMock(ConstraintSetMatchesControlRule::class, $result);
        $rule->method('forMetadataId')->willReturnSelf();
        $rule->method('forControl')->willReturnSelf();
        return $rule;
    }

    private function resourceKindConstraintIsUser(bool $result): ResourceKindConstraintIsUserIfMetadataDeterminesAssigneeRule {
        return $this->createRuleWithFactoryMethodMock(
            ResourceKindConstraintIsUserIfMetadataDeterminesAssigneeRule::class,
            'forMetadataId',
            $result
        );
    }

    protected function setUp() {
        $this->languagesRule = $this->createRuleMock(ContainsOnlyAvailableLanguagesRule::class, true);
        $this->constraintSetRule = $this->constraintSetMatchesControlRule(true);
        $this->constraintArgumentsRule = $this->createRuleMock(ConstraintArgumentsAreValidRule::class, true);
        $this->rkConstraintRule = $this->resourceKindConstraintIsUser(true);
        $languageRepository = $this->createLanguageRepositoryMock(['PL']);
        $this->unknownLanguageStripper = new UnknownLanguageStripper($languageRepository);
        $this->command = new MetadataUpdateCommand(1, ['PL' => 'Test'], [], [], ['resourceKind' => [0]], false);
    }

    public function testPasses() {
        $validator = new MetadataUpdateCommandValidator(
            $this->languagesRule,
            $this->constraintSetRule,
            $this->constraintArgumentsRule,
            $this->rkConstraintRule,
            $this->unknownLanguageStripper
        );
        $validator->validate($this->command);
    }

    public function testFailsWithInvalidLanguages() {
        $this->expectException(InvalidCommandException::class);
        $validator = new MetadataUpdateCommandValidator(
            $this->createRuleMock(ContainsOnlyAvailableLanguagesRule::class, false),
            $this->constraintSetRule,
            $this->constraintArgumentsRule,
            $this->rkConstraintRule,
            $this->unknownLanguageStripper
        );
        $validator->validate($this->command);
    }

    public function testFailsWithInvalidConstraintSet() {
        $this->expectException(InvalidCommandException::class);
        $validator = new MetadataUpdateCommandValidator(
            $this->languagesRule,
            $this->constraintSetMatchesControlRule(false),
            $this->constraintArgumentsRule,
            $this->rkConstraintRule,
            $this->unknownLanguageStripper
        );
        $validator->validate($this->command);
    }

    public function testFailsWithInvalidConstraintArguments() {
        $this->expectException(InvalidCommandException::class);
        $validator = new MetadataUpdateCommandValidator(
            $this->languagesRule,
            $this->constraintSetRule,
            $this->createRuleMock(ConstraintArgumentsAreValidRule::class, false),
            $this->rkConstraintRule,
            $this->unknownLanguageStripper
        );
        $validator->validate($this->command);
    }

    public function testFailsWithInvalidResourceKindConstraint() {
        $this->expectException(InvalidCommandException::class);
        $validator = new MetadataUpdateCommandValidator(
            $this->languagesRule,
            $this->constraintSetRule,
            $this->constraintArgumentsRule,
            $this->resourceKindConstraintIsUser(false),
            $this->unknownLanguageStripper
        );
        $validator->validate($this->command);
    }

    public function testStrippingUnknownLanugagesOnPrepare() {
        $validator = new MetadataUpdateCommandValidator(
            $this->languagesRule,
            $this->constraintSetRule,
            $this->constraintArgumentsRule,
            $this->rkConstraintRule,
            $this->unknownLanguageStripper
        );
        $command = new MetadataUpdateCommand(
            1,
            ['PL' => 'TestLabel', 'EN' => 'TestLabel'],
            ['PL' => 'TestDescription', 'EN' => 'TestDescription'],
            ['PL' => 'TestPlaceholder', 'EN' => 'TestPlaceholder'],
            ['resourceKind' => [0]],
            false
        );
        /** @var MetadataUpdateCommand $preparedCommand */
        $preparedCommand = $validator->prepareCommand($command);
        $this->assertEquals(1, $preparedCommand->getMetadataId());
        $this->assertEquals(['PL' => 'TestLabel'], $preparedCommand->getNewLabel());
        $this->assertEquals(['PL' => 'TestDescription'], $preparedCommand->getNewDescription());
        $this->assertEquals(['PL' => 'TestPlaceholder'], $preparedCommand->getNewPlaceholder());
    }
}
