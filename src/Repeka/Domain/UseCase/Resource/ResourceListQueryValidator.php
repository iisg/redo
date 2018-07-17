<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Rules\ResourceClassExistsRule;
use Repeka\Domain\Validation\Rules\ResourceContentsCorrectStructureRule;
use Repeka\Domain\Validation\Rules\ResourceMetadataSortCorrectStructureRule;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class ResourceListQueryValidator extends CommandAttributesValidator {
    /** @var  ResourceClassExistsRule */
    private $resourceClassExistsRule;
    /** @var ResourceContentsCorrectStructureRule */
    private $resourceContentsCorrectStructureRule;
    /** @var ResourceMetadataSortCorrectStructureRule */
    private $resourceMetadataSortCorrectStructureRule;

    public function __construct(
        ResourceClassExistsRule $resourceClassExistsRule,
        ResourceContentsCorrectStructureRule $resourceContentsCorrectStructureRule,
        ResourceMetadataSortCorrectStructureRule $resourceMetadataSortCorrectStructureRule
    ) {
        $this->resourceClassExistsRule = $resourceClassExistsRule;
        $this->resourceContentsCorrectStructureRule = $resourceContentsCorrectStructureRule;
        $this->resourceMetadataSortCorrectStructureRule = $resourceMetadataSortCorrectStructureRule;
    }

    /**
     * @param ResourceListQuery $command
     * @inheritdoc
     */
    public function getValidator(Command $command): Validatable {
        return Validator
            ::attribute('resourceClasses', Validator::arrayType()->each($this->resourceClassExistsRule))
            ->attribute('resourceKinds', Validator::arrayType()->each(Validator::instance(ResourceKind::class)))
            ->attribute('contentsFilters', Validator::arrayType()->each($this->resourceContentsCorrectStructureRule))
            ->attribute('sortBy', $this->resourceMetadataSortCorrectStructureRule);
    }
}
