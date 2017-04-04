<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Rules\ContainsOnlyAvailableLanguagesRule;
use Repeka\Domain\Validation\Rules\IsValidControlRule;
use Repeka\Domain\Validation\Rules\NotBlankInAllLanguagesRule;
use Respect\Validation\Validator;

class MetadataChildCreateCommandValidator extends CommandAttributesValidator {
    /** @var NotBlankInAllLanguagesRule */
    private $notBlankInAllLanguagesRule;
    /** @var ContainsOnlyAvailableLanguagesRule */
    private $containsOnlyAvailableLanguagesRule;
    /** @var IsValidControlRule */
    private $isValidControlRule;

    public function __construct(
        NotBlankInAllLanguagesRule $notBlankInAllLanguagesRule,
        ContainsOnlyAvailableLanguagesRule $containsOnlyAvailableLanguagesRule,
        IsValidControlRule $isValidControlRule
    ) {
        $this->notBlankInAllLanguagesRule = $notBlankInAllLanguagesRule;
        $this->containsOnlyAvailableLanguagesRule = $containsOnlyAvailableLanguagesRule;
        $this->isValidControlRule = $isValidControlRule;
    }

    /**
     * @inheritdoc
     * @param MetadataChildCreateCommand $command
     */
    public function getValidator(Command $command): Validator {
        return Validator
            ::attribute('newChildMetadata', Validator
                ::key('label', $this->notBlankInAllLanguagesRule)
                ->key('name', Validator::notBlank())
                ->key('placeholder', $this->containsOnlyAvailableLanguagesRule)
                ->key('description', $this->containsOnlyAvailableLanguagesRule)
                ->key('control', $this->isValidControlRule));
    }
}
