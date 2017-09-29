<?php
namespace Repeka\Domain\Workflow;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\ResourceWorkflowPlace;
use Repeka\Domain\Entity\ResourceWorkflowTransition;
use Repeka\Domain\Entity\User;

class UnsatisfiedTransitionExplanation {
    /** @var int[] */
    private $missingMetadataIds = [];
    /** @var bool */
    private $userMissingRequiredRole = false;

    /**
     * @param ResourceWorkflow $workflow
     * Type omitted intentionally for prettier testing
     */
    public function __construct($workflow, ResourceWorkflowTransition $transition, ResourceEntity $resource, User $user) {
        $this->userMissingRequiredRole = !$transition->userHasRoleRequiredToApply($user);
        $this->missingMetadataIds = $this->calculateMissingMetadataIds($workflow, $transition, $resource);
    }

    private function mapToIds(array $entities): array {
        return array_map(function ($transition) {
            return $transition->getId();
        }, $entities);
    }

    /**
     * @param ResourceWorkflow $workflow
     * Type omitted intentionally for prettier testing
     */
    private function calculateMissingMetadataIds($workflow, ResourceWorkflowTransition $transition, ResourceEntity $resource): array {
        $placeIds = $this->mapToIds($workflow->getPlaces());
        $placesById = array_combine($placeIds, $workflow->getPlaces());
        $targetPlaces = array_map(function (string $toId) use ($placesById) {
            return $placesById[$toId];
        }, $transition->getToIds());
        $missingMetadataIds = [];
        foreach ($targetPlaces as $targetPlace) {
            /** @var ResourceWorkflowPlace $targetPlace */
            $metadataIdsMissingForPlace = $targetPlace->getMissingRequiredMetadataIds($resource);
            $missingMetadataIds = array_merge($missingMetadataIds, $metadataIdsMissingForPlace);
        }
        return array_unique($missingMetadataIds);
    }

    /** @return int[] */
    public function getMissingMetadataIds(): array {
        return $this->missingMetadataIds;
    }

    public function isUserMissingRequiredRole(): bool {
        return $this->userMissingRequiredRole;
    }

    public function isSatisfied(): bool {
        return !$this->isUserMissingRequiredRole() && empty($this->missingMetadataIds);
    }
}
