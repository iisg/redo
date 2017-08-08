<?php
namespace Repeka\Domain\UseCase\Language;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class LanguageCreateCommandValidator extends CommandAttributesValidator {
    /**
     * @inheritdoc
     */
    public function getValidator(Command $command): Validatable {
        return Validator
            ::attribute('code', Validator::regex('/^[A-Z]+-?[A-Z]*[A-Z]$/'))
            ->attribute('code', Validator::length(2, 10))
            ->attribute('flag', Validator::notBlank())
            ->attribute('name', Validator::notBlank());
    }
}
