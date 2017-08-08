<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Rules\ResourceHasNoChildrenRule;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class ResourceDeleteCommandValidator extends CommandAttributesValidator {
    /** @var ResourceHasNoChildrenRule */
    private $resourceHasNoChildrenRule;

    public function __construct(ResourceHasNoChildrenRule $resourceHasNoChildrenRule) {
        $this->resourceHasNoChildrenRule = $resourceHasNoChildrenRule;
    }

    public function getValidator(Command $command): Validatable {
        return Validator::attribute('resource', $this->resourceHasNoChildrenRule);
    }
}
