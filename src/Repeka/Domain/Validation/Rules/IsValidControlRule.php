<?php
namespace Repeka\Domain\Validation\Rules;

use Repeka\Domain\Entity\MetadataControl;
use Respect\Validation\Rules\AbstractRule;
use Respect\Validation\Validator;

class IsValidControlRule extends AbstractRule {
    public function validate($input) {
        return Validator::callback(
            function ($value) {
                return MetadataControl::isValid($value);
            }
        )->validate($input);
    }
}
