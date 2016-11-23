<?php
namespace Repeka\Domain\UseCase\Language;

use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Validator;

class LanguageCreateCommandValidator extends CommandAttributesValidator {
    public function getValidator(): \Respect\Validation\Validator {
        return Validator
            ::attribute('code', Validator::regex('/^[A-Z-]*$/'))
            ->attribute('code', Validator::notBlank())
            ->attribute('flag', Validator::notBlank())
            ->attribute('name', Validator::notBlank());
    }
}
