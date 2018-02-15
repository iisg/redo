<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Rules\ResourceClassExistsRule;
use Repeka\Domain\Validation\Rules\ResourceContentsCorrectStructureRule;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class ResourceListQueryValidator extends CommandAttributesValidator {
    /** @var  ResourceClassExistsRule */
    private $resourceClassExistsRule;
    /** @var ResourceContentsCorrectStructureRule */
    private $resourceContentsCorrectStructureRule;

    public function __construct(
        ResourceClassExistsRule $resourceClassExistsRule,
        ResourceContentsCorrectStructureRule $resourceContentsCorrectStructureRule
    ) {
        $this->resourceClassExistsRule = $resourceClassExistsRule;
        $this->resourceContentsCorrectStructureRule = $resourceContentsCorrectStructureRule;
    }

    /**
     * @param ResourceListQuery $command
     * @inheritdoc
     */
    public function getValidator(Command $command): Validatable {
        return Validator
            ::attribute('resourceClasses', Validator::arrayType()->each($this->resourceClassExistsRule))
            ->attribute('resourceKinds', Validator::arrayType()->each(Validator::instance(ResourceKind::class)))
            ->attribute('page', Validator::intVal()->min(0))
            ->attribute('resultsPerPage', Validator::intVal()->min(1))
            ->attribute('contentsFilter', $this->resourceContentsCorrectStructureRule);
    }
}
