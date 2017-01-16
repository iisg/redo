<?php
namespace Repeka\Domain\UseCase\Language;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Validator;

class LanguageCreateCommandValidator extends CommandAttributesValidator {
    /**
     * @inheritdoc
     */
    public function getValidator(Command $command): \Respect\Validation\Validator {
        return Validator
            ::attribute('code', Validator::regex('/^[A-Z]+-?[A-Z]*[A-Z]$/'))
            ->attribute('code', Validator::length($min = 2, $max = 10))
            ->attribute('flag', Validator::notBlank())
            ->attribute('name', Validator::notBlank());
    }
}
