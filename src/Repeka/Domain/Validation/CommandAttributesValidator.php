<?php
namespace Repeka\Domain\Validation;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandValidator;
use Repeka\Domain\Exception\InvalidCommandException;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Exceptions\ValidationException;

abstract class CommandAttributesValidator implements CommandValidator {
    abstract public function getValidator(): \Respect\Validation\Validator;

    /**
     * @inheritdoc
     */
    final public function validate(Command $command) {
        try {
            $this->getValidator()->assert($command);
        } catch (NestedValidationException $compositeException) {
            $violations = array_map([$this, 'exceptionToViolation'], iterator_to_array($compositeException));
            throw new InvalidCommandException($violations, $compositeException);
        }
    }

    final public function isValid(Command $command) {
        return $this->getValidator()->validate($command);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod) it is used as the array notation callback in #validate
     */
    private function exceptionToViolation(ValidationException $exception): array {
        return [
            'field' => $exception->getName(),
            'rule' => $exception->getId(),
            'defaultMessage' => $exception->getMessage(),
        ];
    }
}
