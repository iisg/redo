<?php
namespace Repeka\Domain\Exception;

use Repeka\Domain\Cqrs\Command;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Exceptions\ValidationException;

class RespectValidationFailedException extends InvalidCommandException {
    public function __construct(NestedValidationException $compositeException, ?Command $command = null) {
        $violations = array_map([$this, 'exceptionToViolation'], iterator_to_array($compositeException));
        parent::__construct($command, $violations, $compositeException->getFullMessage(), $compositeException);
    }

    /** @SuppressWarnings(PHPMD.UnusedPrivateMethod) it is used in array notation callback above */
    private function exceptionToViolation(ValidationException $exception): array {
        return [
            'field' => $exception->getName(),
            'rule' => $exception->getId(),
            'message' => $exception->getMessage(),
            'params' => $exception->getParams(),
        ];
    }
}
