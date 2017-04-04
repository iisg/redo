<?php
namespace Repeka\Domain\UseCase\Language;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Respect\Validation\Validator;

class LanguageUpdateCommandValidator extends CommandAttributesValidator {
    /**
     * @inheritdoc
     */
    public function getValidator(Command $command): Validator {
        return Validator
            ::attribute('newFlag', Validator::notBlank())
            ->attribute('newName', Validator::notBlank());
    }
}
