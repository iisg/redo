<?php
namespace Repeka\Domain\UseCase\Validation;

use Repeka\Domain\Validation\MetadataConstraints\ValidPeselConstraint;

class ValidatePeselQueryHandler {

    /** * @var ValidPeselConstraint */
    private $validPeselConstraint;

    public function __construct(ValidPeselConstraint $validPeselConstraint) {
        $this->validPeselConstraint = $validPeselConstraint;
    }

    public function handle(ValidatePeselQuery $query) {
        $this->validPeselConstraint->validatePesel($query->getPesel());
    }
}
