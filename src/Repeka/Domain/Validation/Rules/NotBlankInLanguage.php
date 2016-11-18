<?php
namespace Repeka\Domain\Validation\Rules;

use Repeka\Domain\Validation\Validator;
use Respect\Validation\Rules\AbstractRule;

class NotBlankInLanguage extends AbstractRule {
    private $requiredLanguage;

    public function __construct($requiredLanguage) {
        $this->requiredLanguage = $requiredLanguage;
    }

    public function validate($input) {
        return Validator::arrayType()->key($this->requiredLanguage, Validator::notBlank())->validate($input);
    }
}
