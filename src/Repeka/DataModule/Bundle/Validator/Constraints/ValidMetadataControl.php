<?php
namespace Repeka\DataModule\Bundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidMetadataControl extends Constraint {
    public $message = 'This is not a valid metadata control.';
}
