<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Rules\ContainsUniqueValuesRule;
use Repeka\Domain\Validation\Rules\ResourceClassExistsRule;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class MetadataUpdateOrderCommandValidator extends CommandAttributesValidator {
    /** @var ContainsUniqueValuesRule */
    private $containsUniqueValues;
    /** @var ResourceClassExistsRule */
    private $resourceClassExistsRule;

    public function __construct(ContainsUniqueValuesRule $containsUniqueValues, ResourceClassExistsRule $resourceClassExistsRule) {
        $this->containsUniqueValues = $containsUniqueValues;
        $this->resourceClassExistsRule = $resourceClassExistsRule;
    }

    public function getValidator(Command $command): Validatable {
        return Validator
            ::attribute('metadataIdsInOrder', $this->containsUniqueValues)
            ->attribute('resourceClass', $this->resourceClassExistsRule);
    }
}
