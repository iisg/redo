<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Rules\ResourceClassExistsRule;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class ResourceKindByResourceClassListQueryValidator extends CommandAttributesValidator {

    /** @var ResourceClassExistsRule */
    private $resourceClassExistsRule;

    public function __construct(ResourceClassExistsRule $resourceClassExistsRule) {
        $this->resourceClassExistsRule = $resourceClassExistsRule;
    }

    /**
     * @param ResourceKindListQuery $command
     * @inheritdoc
     */
    public function getValidator(Command $command): Validatable {
        return Validator
            ::attribute('resourceClass', $this->resourceClassExistsRule)
            ->setName('Resource class is not defined');
    }
}
