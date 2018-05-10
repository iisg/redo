<?php
namespace Repeka\Domain\Workflow;

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
}
