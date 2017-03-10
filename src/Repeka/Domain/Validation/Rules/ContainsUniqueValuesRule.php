<?php
namespace Repeka\Domain\Validation\Rules;

use Respect\Validation\Rules\AbstractRule;

// TODO replace this with built-in validator when PR is merged: https://github.com/Respect/Validation/pull/812
class ContainsUniqueValuesRule extends AbstractRule {
    public function validate($input) {
        if (!is_array($input)) {
            return false;
        }
        return count($input) == count(array_unique($input));
    }
}
