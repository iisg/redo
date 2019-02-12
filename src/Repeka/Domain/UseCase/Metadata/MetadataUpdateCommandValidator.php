<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Rules\ConstraintArgumentsAreValidRule;
use Repeka\Domain\Validation\Rules\MetadataGroupExistsRule;
use Repeka\Domain\Validation\Rules\ResourceDisplayStrategySyntaxValidRule;
use Repeka\Domain\Validation\Rules\ResourceKindConstraintIsUserIfMetadataDeterminesAssigneeRule;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class MetadataUpdateCommandValidator extends CommandAttributesValidator {
    /** @var ConstraintArgumentsAreValidRule */
    private $constraintArgumentsAreValidRule;
    /** @var ResourceKindConstraintIsUserIfMetadataDeterminesAssigneeRule */
    private $rkConstraintIsUserIfNecessaryRule;
    /** @var MetadataGroupExistsRule */
    private $metadataGroupExistsRule;
    /** @var ResourceDisplayStrategySyntaxValidRule */
    private $displayStrategySyntaxValidRule;

    public function __construct(
        ConstraintArgumentsAreValidRule $constraintArgumentsAreValidRule,
        ResourceKindConstraintIsUserIfMetadataDeterminesAssigneeRule $rkConstraintIsUserIfNecessaryRule,
        MetadataGroupExistsRule $metadataGroupExistsRule,
        ResourceDisplayStrategySyntaxValidRule $displayStrategySyntaxValidRule
    ) {
        $this->constraintArgumentsAreValidRule = $constraintArgumentsAreValidRule;
        $this->rkConstraintIsUserIfNecessaryRule = $rkConstraintIsUserIfNecessaryRule;
        $this->metadataGroupExistsRule = $metadataGroupExistsRule;
        $this->displayStrategySyntaxValidRule = $displayStrategySyntaxValidRule;
    }

    /** @param MetadataUpdateCommand $command */
    public function getValidator(Command $command): Validatable {
        return Validator
            ::attribute('newLabel', Validator::arrayType())
            ->attribute('newPlaceholder', Validator::arrayType())
            ->attribute('newDescription', Validator::arrayType())
            ->attribute('newShownInBrief', Validator::boolType())
            ->attribute('newCopyToChildResource', Validator::boolType())
            ->attribute('newConstraints', $this->constraintArgumentsAreValidRule)
            ->attribute('newConstraints', $this->rkConstraintIsUserIfNecessaryRule->forMetadata($command->getMetadata()))
            ->attribute('newGroupId', $this->metadataGroupExistsRule)
            ->attribute('newDisplayStrategy', $this->displayStrategySyntaxValidRule);
    }
}
