<?php
namespace Repeka\Tests\Domain\UseCase\Metadata;

use Repeka\Domain\Exception\InvalidCommandException;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateCommand;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateCommandValidator;
use Repeka\Domain\Validation\Rules\ConstraintArgumentsAreValidRule;
use Repeka\Domain\Validation\Rules\ConstraintSetMatchesControlRule;
use Repeka\Domain\Validation\Rules\ContainsOnlyAvailableLanguagesRule;
use Repeka\Domain\Validation\Rules\ResourceKindConstraintIsUserIfMetadataDeterminesAssigneeRule;
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
        $this->command = new MetadataUpdateCommand(1, ['PL' => 'Test'], [], [], ['resourceKind' => [0]], false, false);
    }

    public function testPasses() {
        $validator = new MetadataUpdateCommandValidator(
            $this->languagesRule,
            $this->constraintSetRule,
            $this->constraintArgumentsRule,
            $this->rkConstraintRule
        );
        $validator->validate($this->command);
    }

    public function testFailsWithInvalidLanguages() {
        $this->expectException(InvalidCommandException::class);
        $validator = new MetadataUpdateCommandValidator(
            $this->createRuleMock(ContainsOnlyAvailableLanguagesRule::class, false),
            $this->constraintSetRule,
            $this->constraintArgumentsRule,
            $this->rkConstraintRule
        );
        $validator->validate($this->command);
    }

    public function testFailsWithInvalidConstraintSet() {
        $this->expectException(InvalidCommandException::class);
        $validator = new MetadataUpdateCommandValidator(
            $this->languagesRule,
            $this->constraintSetMatchesControlRule(false),
            $this->constraintArgumentsRule,
            $this->rkConstraintRule
        );
        $validator->validate($this->command);
    }

    public function testFailsWithInvalidConstraintArguments() {
        $this->expectException(InvalidCommandException::class);
        $validator = new MetadataUpdateCommandValidator(
            $this->languagesRule,
            $this->constraintSetRule,
            $this->createRuleMock(ConstraintArgumentsAreValidRule::class, false),
            $this->rkConstraintRule
        );
        $validator->validate($this->command);
    }

    public function testFailsWithInvalidResourceKindConstraint() {
        $this->expectException(InvalidCommandException::class);
        $validator = new MetadataUpdateCommandValidator(
            $this->languagesRule,
            $this->constraintSetRule,
            $this->constraintArgumentsRule,
            $this->resourceKindConstraintIsUser(false)
        );
        $validator->validate($this->command);
    }
}
