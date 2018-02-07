<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Rules\ContainsParentMetadataRule;
use Repeka\Domain\Validation\Rules\CorrectResourceDisplayStrategySyntaxRule;
use Repeka\Domain\Validation\Rules\NotBlankInAllLanguagesRule;
use Repeka\Domain\Validation\Rules\ResourceKindConstraintIsUserIfMetadataDeterminesAssigneeRule;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class ResourceKindUpdateCommandValidator extends CommandAttributesValidator {
    /** @var ResourceKindConstraintIsUserIfMetadataDeterminesAssigneeRule */
    private $rkConstraintIsUserIfNecessaryRule;
    /** @var NotBlankInAllLanguagesRule */
    private $notBlankInAllLanguagesRule;
    /** @var CorrectResourceDisplayStrategySyntaxRule */
    private $correctResourceDisplayStrategySyntaxRule;
    /** @var ContainsParentMetadataRule */
    private $containsParentMetadataRule;

    public function __construct(
        NotBlankInAllLanguagesRule $notBlankInAllLanguagesRule,
        ResourceKindConstraintIsUserIfMetadataDeterminesAssigneeRule $rkConstraintIsUserIfNecessaryRule,
        CorrectResourceDisplayStrategySyntaxRule $correctResourceDisplayStrategyRule,
        ContainsParentMetadataRule $containsParentMetadataRule
    ) {
        $this->notBlankInAllLanguagesRule = $notBlankInAllLanguagesRule;
        $this->rkConstraintIsUserIfNecessaryRule = $rkConstraintIsUserIfNecessaryRule;
        $this->correctResourceDisplayStrategySyntaxRule = $correctResourceDisplayStrategyRule;
        $this->containsParentMetadataRule = $containsParentMetadataRule;
    }

    /** @inheritdoc */
    public function getValidator(Command $command): Validatable {
        return Validator
            ::attribute('label', $this->notBlankInAllLanguagesRule)
            ->attribute('resourceKind', Validator::instance(ResourceKind::class))
            // length 2 because Parent Metadata is obligatory and one chosen by user
            ->attribute('metadataList', Validator::arrayType()->length(2)->each(Validator::callback([$this, 'validateMetadata'])
                ->setTemplate('relationship must be constrained to users because metadata determines assignee')
                ->setName('metadataList')))
            ->attribute('metadataList', $this->containsParentMetadataRule)
            ->attribute('displayStrategies', Validator::arrayType()
                ->each($this->correctResourceDisplayStrategySyntaxRule->setName('displayStrategies')));
    }

    public function validateMetadata(array $metadata) {
        $constraintsValidator = $this->rkConstraintIsUserIfNecessaryRule->forMetadataId($metadata['baseId']);
        return Validator::key('constraints', $constraintsValidator, false)->validate($metadata);
    }
}
