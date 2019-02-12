<?php
namespace Repeka\Tests\Domain\UseCase\Metadata;

use Repeka\Domain\Exception\InvalidCommandException;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateCommand;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateCommandValidator;
use Repeka\Domain\Validation\Rules\ConstraintArgumentsAreValidRule;
use Repeka\Domain\Validation\Rules\MetadataGroupExistsRule;
use Repeka\Domain\Validation\Rules\ResourceDisplayStrategySyntaxValidRule;
use Repeka\Domain\Validation\Rules\ResourceKindConstraintIsUserIfMetadataDeterminesAssigneeRule;
use Repeka\Tests\Traits\StubsTrait;

class MetadataUpdateCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ConstraintArgumentsAreValidRule|\PHPUnit_Framework_MockObject_MockObject */
    private $constraintArgumentsRule;
    /** @var ResourceKindConstraintIsUserIfMetadataDeterminesAssigneeRule|\PHPUnit_Framework_MockObject_MockObject */
    private $rkConstraintRule;
    /** @var MetadataGroupExistsRule|\PHPUnit_Framework_MockObject_MockObject */
    private $metadataGroupExistsRule;
    /** @var ResourceDisplayStrategySyntaxValidRule|\PHPUnit_Framework_MockObject_MockObject */
    private $displayStrategySyntaxValidRule;

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
        $this->metadataGroupExistsRule = $this->createRuleMock(MetadataGroupExistsRule::class, true);
        $this->displayStrategySyntaxValidRule = $this->createRuleMock(ResourceDisplayStrategySyntaxValidRule::class, true);
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
            $this->metadataGroupExistsRule,
            $this->displayStrategySyntaxValidRule
        );
        $validator->validate($this->command);
    }

    public function testFailsWithInvalidConstraintArguments() {
        $this->expectException(InvalidCommandException::class);
        $validator = new MetadataUpdateCommandValidator(
            $this->createRuleMock(ConstraintArgumentsAreValidRule::class, false),
            $this->rkConstraintRule,
            $this->metadataGroupExistsRule,
            $this->displayStrategySyntaxValidRule
        );
        $validator->validate($this->command);
    }

    public function testFailsWithInvalidResourceKindConstraint() {
        $this->expectException(InvalidCommandException::class);
        $validator = new MetadataUpdateCommandValidator(
            $this->constraintArgumentsRule,
            $this->resourceKindConstraintIsUser(false),
            $this->metadataGroupExistsRule,
            $this->displayStrategySyntaxValidRule
        );
        $validator->validate($this->command);
    }

    public function testFailsWithNonexistingGroup() {
        $this->expectException(InvalidCommandException::class);
        $validator = new MetadataUpdateCommandValidator(
            $this->constraintArgumentsRule,
            $this->rkConstraintRule,
            $this->createRuleMock(MetadataGroupExistsRule::class, false),
            $this->displayStrategySyntaxValidRule
        );
        $validator->validate($this->command);
    }

    public function testFailsWithInvalidDisplayStrategy() {
        $this->expectException(InvalidCommandException::class);
        $validator = new MetadataUpdateCommandValidator(
            $this->constraintArgumentsRule,
            $this->rkConstraintRule,
            $this->metadataGroupExistsRule,
            $this->createRuleMock(ResourceDisplayStrategySyntaxValidRule::class, false)
        );
        $validator->validate($this->command);
    }
}
