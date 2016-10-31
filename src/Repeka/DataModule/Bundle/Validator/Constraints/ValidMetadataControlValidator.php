<?php
namespace Repeka\DataModule\Bundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ValidMetadataControlValidator extends ConstraintValidator {
    /**
     * @var array
     */
    private $supportedControls;

    public function __construct(array $supportedControls) {
        $this->supportedControls = $supportedControls;
    }

    /**
     * @inheritdoc
     */
    public function validate($value, Constraint $constraint) {
        if ($value && !in_array($value, $this->supportedControls)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
