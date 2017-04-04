<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Rules\NotBlankInAllLanguagesRule;
use Respect\Validation\Validator;

class ResourceKindCreateCommandValidator extends CommandAttributesValidator {
    /** @var NotBlankInAllLanguagesRule */
    private $notBlankInAllLanguagesRule;

    public function __construct(NotBlankInAllLanguagesRule $notBlankInAllLanguagesRule) {
        $this->notBlankInAllLanguagesRule = $notBlankInAllLanguagesRule;
    }

    /**
     * @inheritdoc
     */
    public function getValidator(Command $command): Validator {
        return Validator
            ::attribute('label', $this->notBlankInAllLanguagesRule)
            ->attribute('metadataList', Validator::arrayType()->length(1)->each(
                Validator::arrayType()->length(1)->key('baseId', Validator::intVal())
            ));
    }
}
