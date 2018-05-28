<?php
namespace Repeka\Domain\Validation\Rules;

use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
use Respect\Validation\Rules\AbstractRule;
use Respect\Validation\Validator;

class NoAssigneeMetadataInFirstPlaceRule extends AbstractRule {

    public function validate($input): bool {
        return Validator::callback([$this, 'noAssigneeMetadataInFirstPlace'])->validate($input);
    }

    public function noAssigneeMetadataInFirstPlace($places) {
        $firstPlace = $places[0] instanceof ResourceWorkflowPlace ? $places[0]->toArray() : $places[0];
        return !key_exists('assigneeMetadataIds', $firstPlace) || count($firstPlace['assigneeMetadataIds']) == 0;
    }
}
