<?php
namespace Repeka\Domain\Validation\Rules;

use Repeka\Domain\Validation\Validator;
use Respect\Validation\Rules\AbstractRule;

class NotBlankInAllLanguages extends AbstractRule {
    private $requiredLanguagesChecks;

    private $requiredLanguagesCount;

    public function __construct(array $availableLanguages) {
        $this->requiredLanguagesChecks = array_map(function ($code) {
            return Validator::key($code, Validator::notBlank());
        }, $availableLanguages);
        $this->requiredLanguagesCount = count($availableLanguages);
    }

    public function validate($input) {
        return Validator::allOf(
            Validator::arrayType(),
            Validator::length($this->requiredLanguagesCount, $this->requiredLanguagesCount),
            $this->requiredLanguagesChecks
        )->validate($input);
    }
}
