<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Rules\ContainsOnlyAvailableLanguagesRule;
use Respect\Validation\Validator;

class MetadataUpdateCommandValidator extends CommandAttributesValidator {
    /** @var ContainsOnlyAvailableLanguagesRule */
    private $containsOnlyAvailableLanguagesRule;

    public function __construct(ContainsOnlyAvailableLanguagesRule $containsOnlyAvailableLanguagesRule) {
        $this->containsOnlyAvailableLanguagesRule = $containsOnlyAvailableLanguagesRule;
    }

    /**
     * @inheritdoc
     */
    public function getValidator(Command $command): Validator {
        return Validator
            ::attribute('metadataId', Validator::intVal()->min(1))
            ->attribute('newLabel', $this->containsOnlyAvailableLanguagesRule)
            ->attribute('newPlaceholder', $this->containsOnlyAvailableLanguagesRule)
            ->attribute('newDescription', $this->containsOnlyAvailableLanguagesRule);
    }
}
