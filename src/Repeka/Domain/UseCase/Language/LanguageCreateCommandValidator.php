<?php
namespace Repeka\Domain\UseCase\Language;

use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Validator;

class LanguageCreateCommandValidator extends CommandAttributesValidator {

    protected function getValidator(): Validator {
        return Validator
            ::attribute('flag', Validator::notBlank())
            ->attribute('name', Validator::notBlank());
    }
}
