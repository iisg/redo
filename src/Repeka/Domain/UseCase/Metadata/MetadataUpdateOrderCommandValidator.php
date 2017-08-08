<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Rules\ContainsUniqueValuesRule;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class MetadataUpdateOrderCommandValidator extends CommandAttributesValidator {
    /** @var ContainsUniqueValuesRule */
    private $containsUniqueValues;

    public function __construct(ContainsUniqueValuesRule $containsUniqueValues) {
        $this->containsUniqueValues = $containsUniqueValues;
    }

    public function getValidator(Command $command): Validatable {
        return Validator::attribute('metadataIdsInOrder', $this->containsUniqueValues);
    }
}
