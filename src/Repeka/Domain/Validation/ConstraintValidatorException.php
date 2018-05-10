<?php
namespace Repeka\Domain\Validation;

class ConstraintValidatorException extends \Exception {
    public function __construct($message) {
        parent::__construct($message);
    }
}
