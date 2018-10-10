<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Exception\DomainException;
use Repeka\Domain\Utils\EntityUtils;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class ResourceListFtsQueryValidator extends CommandAttributesValidator {
    /**
     * @param ResourceListFtsQuery $command
     * @inheritdoc
     */
    public function getValidator(Command $command): Validatable {
        return Validator
            ::attribute('facetsFilters', Validator::arrayType()->each(Validator::arrayType()->each(Validator::numericVal())))
            ->attribute('facetsFilters', Validator::callback($this->allowOnlyWhitelistedFacetFilters($command)));
    }

    private function allowOnlyWhitelistedFacetFilters(ResourceListFtsQuery $command): callable {
        return function (array $filters) use ($command) {
            $filteredKeys = array_keys($filters);
            $allowedKeys = EntityUtils::mapToIds($command->getFacetedMetadata());
            if ($command->hasResourceKindFacet()) {
                $allowedKeys[] = 'kindId';
            }
            $forbiddenKeys = array_diff($filteredKeys, $allowedKeys);
            if ($forbiddenKeys) {
                throw new DomainException(
                    "You cannot filter this query by facets: " . implode(', ', $forbiddenKeys) . '. They are not calculated in this query.'
                );
            } else {
                return true;
            }
        };
    }
}
