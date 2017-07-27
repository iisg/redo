<?php
namespace Repeka\Domain\Validation\Rules;

use Repeka\Domain\Entity\Workflow\ResourceWorkflowTransition;
use Respect\Validation\Rules\AbstractRule;
use Respect\Validation\Validator;

class WorkflowTransitionsDefinitionIsValidRule extends AbstractRule {

    public function validate($input): bool {
        return Validator::arrayType()->each(Validator::oneOf(
            Validator::instance(ResourceWorkflowTransition::class),
            Validator::arrayType()->keySet(
                Validator::key('label', Validator::arrayType()),
                Validator::key('froms', Validator::arrayType()),
                Validator::key('tos', Validator::arrayType()),
                Validator::key('permittedRoleIds', Validator::arrayType(), false),
                Validator::key('id', Validator::stringType(), false)
            )
        ))->validate($input);
    }
}
