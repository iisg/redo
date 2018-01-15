<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Rules\CorrectResourceDisplayStrategySyntaxRule;
use Repeka\Domain\Validation\Rules\NotBlankInAllLanguagesRule;
use Repeka\Domain\Validation\Rules\ResourceKindConstraintIsUserIfMetadataDeterminesAssigneeRule;
use Repeka\Domain\Validation\Strippers\UnknownLanguageStripper;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class ResourceKindUpdateCommandValidator extends CommandAttributesValidator {
    /** @var ResourceKindConstraintIsUserIfMetadataDeterminesAssigneeRule */
    private $rkConstraintIsUserIfNecessaryRule;
    /** @var NotBlankInAllLanguagesRule */
    private $notBlankInAllLanguagesRule;
    /** @var CorrectResourceDisplayStrategySyntaxRule */
    private $correctResourceDisplayStrategySyntaxRule;
    /** @var UnknownLanguageStripper */
    private $unknownLanguageStripper;

    public function __construct(
        NotBlankInAllLanguagesRule $notBlankInAllLanguagesRule,
        ResourceKindConstraintIsUserIfMetadataDeterminesAssigneeRule $rkConstraintIsUserIfNecessaryRule,
        CorrectResourceDisplayStrategySyntaxRule $correctResourceDisplayStrategyRule,
        UnknownLanguageStripper $unknownLanguageStripper
    ) {
        $this->notBlankInAllLanguagesRule = $notBlankInAllLanguagesRule;
        $this->rkConstraintIsUserIfNecessaryRule = $rkConstraintIsUserIfNecessaryRule;
        $this->correctResourceDisplayStrategySyntaxRule = $correctResourceDisplayStrategyRule;
        $this->unknownLanguageStripper = $unknownLanguageStripper;
    }

    /** @inheritdoc */
    public function getValidator(Command $command): Validatable {
        return Validator
            ::attribute('label', $this->notBlankInAllLanguagesRule)
            ->attribute('metadataList', Validator::each(Validator::callback([$this, 'validateMetadata'])
                ->setTemplate('relationship must be constrained to users because metadata determines assignee')
                ->setName('metadataList')))
            ->attribute('displayStrategies', Validator::arrayType()
                ->each($this->correctResourceDisplayStrategySyntaxRule->setName('displayStrategies')));
    }

    public function validateMetadata(array $metadata) {
        $constraintsValidator = $this->rkConstraintIsUserIfNecessaryRule->forMetadataId($metadata['baseId']);
        return Validator::key('constraints', $constraintsValidator, false)->validate($metadata);
    }

    /**
     * @param ResourceKindUpdateCommand $command
     */
    public function prepareCommand(Command $command): Command {
        return new ResourceKindUpdateCommand(
            $command->getResourceKindId(),
            $this->unknownLanguageStripper->removeUnknownLanguages($command->getLabel()),
            $command->getMetadataList(),
            $command->getDisplayStrategies()
        );
    }
}
