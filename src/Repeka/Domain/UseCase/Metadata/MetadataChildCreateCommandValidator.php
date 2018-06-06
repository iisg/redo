<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Rules\IsValidControlRule;
use Repeka\Domain\Validation\Rules\NotBlankInAllLanguagesRule;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class MetadataChildCreateCommandValidator extends CommandAttributesValidator {
    /** @var NotBlankInAllLanguagesRule */
    private $notBlankInAllLanguagesRule;
    /** @var IsValidControlRule */
    private $isValidControlRule;

    public function __construct(
        NotBlankInAllLanguagesRule $notBlankInAllLanguagesRule,
        IsValidControlRule $isValidControlRule
    ) {
        $this->notBlankInAllLanguagesRule = $notBlankInAllLanguagesRule;
        $this->isValidControlRule = $isValidControlRule;
    }

    /**
     * @inheritdoc
     * @param MetadataChildCreateCommand $command
     */
    public function getValidator(Command $command): Validatable {
        return Validator
            ::attribute(
                'newChildMetadata',
                Validator
                    ::key('label', $this->notBlankInAllLanguagesRule)
                    ->key('name', Validator::notBlank())
                    ->key('placeholder', Validator::arrayType())
                    ->key('description', Validator::arrayType())
                    ->key('control', $this->isValidControlRule)
            );
    }
}
