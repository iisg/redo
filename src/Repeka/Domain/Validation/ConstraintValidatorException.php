<?php
namespace Repeka\Domain\Validation;

use Exception;

class ConstraintValidatorException extends \Exception {
    public function __construct($message) {
        parent::__construct($message);
    }
}
