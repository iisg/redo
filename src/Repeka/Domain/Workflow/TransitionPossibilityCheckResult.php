<?php
namespace Repeka\Domain\Workflow;

use Respect\Validation\Validator;

class TransitionPossibilityCheckResult {
    /** @var int[] */
    private $missingMetadataIds;
    /** @var bool */
    private $userMissingRequiredRole;
    /** @var bool */
    private $otherUserAssigned;

    public function __construct(array $missingMetadataIds, bool $userMissingRequiredRole, bool $otherUserAssigned) {
        $this->missingMetadataIds = $missingMetadataIds;
        $this->userMissingRequiredRole = $userMissingRequiredRole;
        $this->otherUserAssigned = $otherUserAssigned;
    }

    public function isTransitionPossible() {
        return empty($this->missingMetadataIds)
            && !$this->userMissingRequiredRole
            && !$this->otherUserAssigned;
    }

    /** @return int[] */
    public function getMissingMetadataIds(): array {
        return $this->missingMetadataIds;
    }

    public function isUserMissingRequiredRole(): bool {
        return $this->userMissingRequiredRole;
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
                ->attribute('userMissingRequiredRole', Validator::falseVal()->setTemplate('Executor does not have required role'))
                ->attribute('otherUserAssigned', Validator::falseVal()->setTemplate('Other user is assigned to this transition'))
                ->assert($this);
        }
    }
}
