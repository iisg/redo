<?php
namespace Repeka\DataModule\Bundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class RequiredInMainLanguage extends Constraint {
    public $message = 'This value is required in the main language.';
}
