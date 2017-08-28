<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Rules\NoResourcesOfKindExistRule;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class ResourceKindDeleteCommandValidator extends CommandAttributesValidator {
    /** @var NoResourcesOfKindExistRule */
    private $noResourcesOfKindExistRule;

    public function __construct(NoResourcesOfKindExistRule $noResourcesOfKindExistRule) {
        $this->noResourcesOfKindExistRule = $noResourcesOfKindExistRule;
    }

    public function getValidator(Command $command): Validatable {
        return Validator::attribute('resourceKind', $this->noResourcesOfKindExistRule);
    }
}
