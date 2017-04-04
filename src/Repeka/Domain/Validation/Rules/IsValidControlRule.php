<?php
namespace Repeka\Domain\Validation\Rules;

use Respect\Validation\Rules\AbstractRule;
use Respect\Validation\Validator;

class IsValidControlRule extends AbstractRule {
    /** @var array */
    private $supportedControls;

    public function __construct(array $supportedControls) {
        $this->supportedControls = $supportedControls;
    }

    public function validate($input) {
        return Validator::in($this->supportedControls)->validate($input);
    }
}
