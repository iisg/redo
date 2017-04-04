<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Rules\ContainsOnlyAvailableLanguagesRule;
use Repeka\Domain\Validation\Rules\IsValidControlRule;
use Repeka\Domain\Validation\Rules\NotBlankInAllLanguagesRule;
use Respect\Validation\Validator;

class MetadataCreateCommandValidator extends CommandAttributesValidator {
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
     */
    public function getValidator(Command $command): Validator {
        return Validator
            ::attribute('label', $this->notBlankInAllLanguagesRule)
            ->attribute('name', Validator::notBlank())
            ->attribute('placeholder', $this->containsOnlyAvailableLanguagesRule)
            ->attribute('description', $this->containsOnlyAvailableLanguagesRule)
            ->attribute('control', $this->isValidControlRule);
    }
}
