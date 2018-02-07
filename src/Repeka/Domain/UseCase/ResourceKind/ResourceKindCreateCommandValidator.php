<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Rules\ContainsParentMetadataRule;
use Repeka\Domain\Validation\Rules\CorrectResourceDisplayStrategySyntaxRule;
use Repeka\Domain\Validation\Rules\NotBlankInAllLanguagesRule;
use Repeka\Domain\Validation\Rules\ResourceClassExistsRule;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class ResourceKindCreateCommandValidator extends CommandAttributesValidator {
    /** @var NotBlankInAllLanguagesRule */
    private $notBlankInAllLanguagesRule;
    /** @var  ResourceClassExistsRule */
    private $resourceClassExistsRule;
    /** @var CorrectResourceDisplayStrategySyntaxRule */
    private $correctResourceDisplayStrategySyntaxRule;
    /** @var ContainsParentMetadataRule */
    private $containsParentMetadataRule;

    public function __construct(
        NotBlankInAllLanguagesRule $notBlankInAllLanguagesRule,
        ResourceClassExistsRule $resourceClassExistsRule,
        CorrectResourceDisplayStrategySyntaxRule $correctResourceDisplayStrategyRule,
        ContainsParentMetadataRule $containsParentMetadataRule
    ) {
        $this->notBlankInAllLanguagesRule = $notBlankInAllLanguagesRule;
        $this->resourceClassExistsRule = $resourceClassExistsRule;
        $this->correctResourceDisplayStrategySyntaxRule = $correctResourceDisplayStrategyRule;
        $this->containsParentMetadataRule = $containsParentMetadataRule;
    }

    /**
     * @param ResourceKindCreateCommand $command
     * @inheritdoc
     */
    public function getValidator(Command $command): Validatable {
        return Validator
            ::attribute('label', $this->notBlankInAllLanguagesRule)
            ->attribute('resourceClass', $this->resourceClassExistsRule)
            // length 2 because Parent Metadata is obligatory and one chosen by user
            ->attribute('metadataList', Validator::arrayType()->length(2)->each(
                Validator::arrayType()->length(1)->key('baseId', Validator::intVal())
            ))
            ->attribute('metadataList', $this->containsParentMetadataRule)
            ->attribute('displayStrategies', Validator::arrayType()->each($this->correctResourceDisplayStrategySyntaxRule));
    }
}
