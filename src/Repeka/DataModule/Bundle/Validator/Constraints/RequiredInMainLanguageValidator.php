<?php
namespace Repeka\DataModule\Bundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class RequiredInMainLanguageValidator extends ConstraintValidator {
    /** @var \Repeka\DataModule\Domain\Validation\RequiredInMainLanguageValidator */
    private $validator;

    public function __construct(array $supportedLanguages) {
        $this->validator = new \Repeka\DataModule\Domain\Validation\RequiredInMainLanguageValidator($supportedLanguages[0]);
    }

    /**
     * @inheritdoc
     */
    public function validate($value, Constraint $constraint) {
        if (!$this->validator->isValid($value)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
