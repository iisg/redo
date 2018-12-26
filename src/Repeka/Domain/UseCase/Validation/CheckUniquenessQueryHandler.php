<?php
namespace Repeka\Domain\UseCase\Validation;

use Repeka\Domain\Validation\MetadataConstraints\UniqueInResourceClassConstraint;

class CheckUniquenessQueryHandler {

    /** @var UniqueInResourceClassConstraint */
    private $uniqueConstraint;

    public function __construct(UniqueInResourceClassConstraint $uniqueConstraint) {
        $this->uniqueConstraint = $uniqueConstraint;
    }

    public function handle(CheckUniquenessQuery $query) {
        $this->uniqueConstraint->validateIsUnique(
            $query->getMetadataId(),
            $query->getMetadataValue(),
            $query->getResourceClass(),
            $query->getResourceId()
        );
    }
}
