<?php
namespace Repeka\Domain\Workflow;

use Respect\Validation\Validator;

class TransitionPossibilityCheckResult {
    /** @var int[] */
    private $missingMetadataIds;
    /** @var bool */
    private $otherUserAssigned;

    public function __construct(array $missingMetadataIds, bool $otherUserAssigned) {
        $this->missingMetadataIds = $missingMetadataIds;
        $this->otherUserAssigned = $otherUserAssigned;
    }

    public function isTransitionPossible() {
        return empty($this->missingMetadataIds)
            && !$this->otherUserAssigned;
    }

    /** @return int[] */
    public function getMissingMetadataIds(): array {
        return $this->missingMetadataIds;
    }

    public function isOtherUserAssigned(): bool {
        return $this->otherUserAssigned;
    }

    public function assertTransitionIsPossible() {
        if (!$this->isTransitionPossible()) {
            Validator
                ::attribute(
                    'missingMetadataIds',
                    Validator::not(Validator::notEmpty()->setTemplate('Some of required metadata values does not have their values'))
                )
                ->attribute('otherUserAssigned', Validator::falseVal()->setTemplate('Other user is assigned to this transition'))
                ->assert($this);
        }
    }
}
