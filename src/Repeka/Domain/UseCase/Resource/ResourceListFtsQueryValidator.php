<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\Metadata;
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
            ::attribute(
                'facetsFilters',
                Validator::arrayType()->each(
                    Validator::arrayType()->keySet(
                        Validator::key(0, Validator::anyOf(Validator::equals('kindId'), Validator::instance(Metadata::class))),
                        Validator::key(1, Validator::arrayType())
                    )
                )
            )
            ->attribute('facetsFilters', Validator::callback($this->allowOnlyWhitelistedFacetFilters($command)));
    }

    private function allowOnlyWhitelistedFacetFilters(ResourceListFtsQuery $command): callable {
        return function (array $filters) use ($command) {
            $allowedKeys = EntityUtils::mapToIds($command->getFacetedMetadata());
            if ($command->hasResourceKindFacet()) {
                $allowedKeys[] = 'kindId';
            }
            $filteredKeys = array_map(
                function ($filter) {
                    list($aggregationName,) = $filter;
                    if ($aggregationName instanceof Metadata) {
                        $aggregationName = $aggregationName->getId();
                    }
                    return $aggregationName;
                },
                $filters
            );
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
