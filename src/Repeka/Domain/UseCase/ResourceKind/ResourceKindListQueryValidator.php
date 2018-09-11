<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Rules\ResourceClassExistsRule;
use Repeka\Domain\Validation\Rules\ResourceMetadataSortCorrectStructureRule;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class ResourceKindListQueryValidator extends CommandAttributesValidator {

    /** @var ResourceClassExistsRule */
    private $resourceClassExistsRule;
    private $resourceMetadataSortCorrectStructureRule;

    public function __construct(
        ResourceClassExistsRule $resourceClassExistsRule,
        ResourceMetadataSortCorrectStructureRule $resourceMetadataSortCorrectStructureRule
    ) {
        $this->resourceClassExistsRule = $resourceClassExistsRule;
        $this->resourceMetadataSortCorrectStructureRule = $resourceMetadataSortCorrectStructureRule;
    }

    /**
     * @param ResourceKindListQuery $command
     * @inheritdoc
     */
    public function getValidator(Command $command): Validatable {
        return Validator
            ::attribute('resourceClasses', Validator::arrayType()->each($this->resourceClassExistsRule))
            ->attribute('ids', Validator::arrayType())
            ->attribute('metadataId', Validator::intVal())
            ->attribute('sortBy', $this->resourceMetadataSortCorrectStructureRule);
    }
}
