<?php
namespace Repeka\Domain\Workflow;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\ResourceWorkflowPlace;
use Repeka\Domain\Entity\ResourceWorkflowTransition;
use Repeka\Domain\Entity\User;

class ResourceWorkflowTransitionHelper {
    /** @var ResourceWorkflow */
    private $workflow;

    public function __construct(ResourceWorkflow $workflow) {
        $this->workflow = $workflow;
    }

    /** @return ResourceWorkflowPlace[] */
    public function getPlacesPermittedByResourceMetadata(ResourceEntity $resource): array {
        $places = $this->workflow->getPlaces();
        return array_values(array_filter($places, function (ResourceWorkflowPlace $place) use ($resource) {
            return $place->isRequiredMetadataFilled($resource);
        }));
    }

    public function placeIsPermittedByResourceMetadata(string $placeId, ResourceEntity $resource): bool {
        $permittedPlaces = $this->getPlacesPermittedByResourceMetadata($resource);
        $permittedIds = $this->mapToIds($permittedPlaces);
        return in_array($placeId, $permittedIds);
    }

    /** @return ResourceWorkflowTransition[] */
    public function getTransitionsPermittedByRole(User $user): array {
        $transitions = $this->workflow->getTransitions();
        return array_values(array_filter($transitions, function (ResourceWorkflowTransition $transition) use ($user) {
            return $transition->userHasRoleRequiredToApply($user);
        }));
    }

    /** @return ResourceWorkflowTransition[] */
    public function getPossibleTransitions(ResourceEntity $resource, User $user): array {
        $permittedTargets = $this->getPlacesPermittedByResourceMetadata($resource);
        $transitionsPermittedByRole = $this->getTransitionsPermittedByRole($user);
        $possibleTransitions = [];
        foreach ($transitionsPermittedByRole as $transition) {
            if ($transition->canEnterTos($permittedTargets)) {
                $possibleTransitions[] = $transition;
            }
        }
        return $possibleTransitions;
    }

    public function transitionIsPossible(string $transitionId, ResourceEntity $resource, User $user): bool {
        $possibleTransitions = $this->getPossibleTransitions($resource, $user);
        $possibleIds = $this->mapToIds($possibleTransitions);
        return in_array($transitionId, $possibleIds);
    }

    private function mapToIds(array $entities): array {
        return array_map(function ($transition) {
            return $transition->getId();
        }, $entities);
    }
}
