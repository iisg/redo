<?php
namespace Repeka\Domain\Validation;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandValidator;
use Repeka\Domain\Exception\RespectValidationFailedException;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validatable;

/** @SuppressWarnings(PHPMD.NumberOfChildren) */
abstract class CommandAttributesValidator extends CommandValidator {
    abstract public function getValidator(Command $command): Validatable;

    final public function validate(Command $command) {
        try {
            $this->getValidator($command)->assert($command);
        } catch (NestedValidationException $e) {
            throw new RespectValidationFailedException($e, $command);
        }
    }

    final public function isValid(Command $command) {
        return $this->getValidator($command)->validate($command);
    }
}
