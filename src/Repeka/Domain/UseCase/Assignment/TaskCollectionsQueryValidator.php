<?php
namespace Repeka\Domain\UseCase\Assignment;

use Repeka\Domain\Constants\TaskStatus;
use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\UseCase\Resource\ResourceListQueryValidator;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Exceptions\ResourceClassExistsRuleException;
use Repeka\Domain\Validation\Rules\ResourceClassExistsRule;
use Repeka\Domain\Validation\Rules\ResourceMetadataSortCorrectStructureRule;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class TaskCollectionsQueryValidator extends CommandAttributesValidator {
    /** @var ResourceListQueryValidator */
    private $resourceListQueryValidator;
    /** @var ResourceClassExistsRule */
    private $resourceClassExistsRule;
    /** @var ResourceMetadataSortCorrectStructureRule */
    private $resourceMetadataSortCorrectStructureRule;

    public function __construct(
        ResourceListQueryValidator $resourceListQueryValidator,
        ResourceClassExistsRule $resourceClassExistsRule,
        ResourceMetadataSortCorrectStructureRule $resourceMetadataSortCorrectStructureRule
    ) {
        $this->resourceListQueryValidator = $resourceListQueryValidator;
        $this->resourceClassExistsRule = $resourceClassExistsRule;
        $this->resourceMetadataSortCorrectStructureRule = $resourceMetadataSortCorrectStructureRule;
    }

    /**
     * @param ResourceListQuery $command
     * @inheritdoc
     */
    public function getValidator(Command $command): Validatable {
        return Validator
            ::attribute('singleCollectionQueries', Validator::arrayType()->callback([$this, 'collectionQueriesValid']));
    }

    /** @param ResourceListQuery[][] $collectionQueries */
    public function collectionQueriesValid(array $collectionQueries) {
        foreach ($collectionQueries as $resourceClass => $collectionQueryByStatus) {
            if (!$this->resourceClassExistsRule->validate($resourceClass)) {
                throw new ResourceClassExistsRuleException();
            }
            foreach ($collectionQueryByStatus as $taskStatus => $collectionQuery) {
                if (!TaskStatus::isValid($taskStatus)) {
                    return false;
                }
                $this->resourceListQueryValidator->validate($collectionQuery);
            }
        }
        return true;
    }
}
