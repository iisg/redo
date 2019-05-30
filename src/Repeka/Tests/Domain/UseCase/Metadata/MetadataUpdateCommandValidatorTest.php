<?php
namespace Repeka\Tests\Domain\UseCase\Metadata;

use Repeka\Domain\Exception\InvalidCommandException;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateCommand;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateCommandValidator;
use Repeka\Domain\Validation\Rules\ConstraintArgumentsAreValidRule;
use Repeka\Domain\Validation\Rules\NotBlankInAllLanguagesRule;
use Repeka\Domain\Validation\Rules\ResourceDisplayStrategySyntaxValidRule;
use Repeka\Domain\Validation\Rules\ResourceKindConstraintIsUserIfMetadataDeterminesAssigneeRule;
use Repeka\Tests\Traits\StubsTrait;

class MetadataUpdateCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ConstraintArgumentsAreValidRule|\PHPUnit_Framework_MockObject_MockObject */
    private $constraintArgumentsRule;
    /** @var ResourceKindConstraintIsUserIfMetadataDeterminesAssigneeRule|\PHPUnit_Framework_MockObject_MockObject */
    private $rkConstraintRule;
    /** @var ResourceDisplayStrategySyntaxValidRule|\PHPUnit_Framework_MockObject_MockObject */
    private $displayStrategySyntaxValidRule;
    /** @var NotBlankInAllLanguagesRule | \PHPUnit_Framework_MockObject_MockObject */
    private $notBlankInAllLanguagesRule;

    /** @var MetadataUpdateCommand */
    private $command;

    private function resourceKindConstraintIsUser(bool $result): ResourceKindConstraintIsUserIfMetadataDeterminesAssigneeRule {
        return $this->createRuleWithFactoryMethodMock(
            ResourceKindConstraintIsUserIfMetadataDeterminesAssigneeRule::class,
            'forMetadata',
            $result
        );
    }

    protected function setUp() {
        $this->constraintArgumentsRule = $this->createRuleMock(ConstraintArgumentsAreValidRule::class, true);
        $this->rkConstraintRule = $this->resourceKindConstraintIsUser(true);
        $this->displayStrategySyntaxValidRule = $this->createRuleMock(ResourceDisplayStrategySyntaxValidRule::class, true);
        $this->notBlankInAllLanguagesRule = $this->createRuleMock(NotBlankInAllLanguagesRule::class, true);
        $this->command = new MetadataUpdateCommand(
            $this->createMetadataMock(),
            ['PL' => 'Test'],
            [],
            [],
            ['resourceKind' => [0]],
            '',
            null,
            false,
            false
        );
    }

    public function testPasses() {
        $validator = new MetadataUpdateCommandValidator(
            $this->constraintArgumentsRule,
            $this->rkConstraintRule,
            $this->displayStrategySyntaxValidRule,
            $this->notBlankInAllLanguagesRule
        );
        $validator->validate($this->command);
    }

    public function testFailsWithInvalidConstraintArguments() {
        $this->expectException(InvalidCommandException::class);
        $validator = new MetadataUpdateCommandValidator(
            $this->createRuleMock(ConstraintArgumentsAreValidRule::class, false),
            $this->rkConstraintRule,
            $this->displayStrategySyntaxValidRule,
            $this->notBlankInAllLanguagesRule
        );
        $validator->validate($this->command);
    }

    public function testFailsWithInvalidResourceKindConstraint() {
        $this->expectException(InvalidCommandException::class);
        $validator = new MetadataUpdateCommandValidator(
            $this->constraintArgumentsRule,
            $this->resourceKindConstraintIsUser(false),
            $this->displayStrategySyntaxValidRule,
            $this->notBlankInAllLanguagesRule
        );
        $validator->validate($this->command);
    }

    public function testFailsWithInvalidDisplayStrategy() {
        $this->expectException(InvalidCommandException::class);
        $validator = new MetadataUpdateCommandValidator(
            $this->constraintArgumentsRule,
            $this->rkConstraintRule,
            $this->createRuleMock(ResourceDisplayStrategySyntaxValidRule::class, false),
            $this->notBlankInAllLanguagesRule
        );
        $validator->validate($this->command);
    }
}
