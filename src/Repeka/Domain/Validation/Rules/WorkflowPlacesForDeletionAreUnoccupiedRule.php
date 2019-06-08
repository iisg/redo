<?php
namespace Repeka\Domain\Validation\Rules;

use Assert\Assertion;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindListQuery;
use Respect\Validation\Rules\AbstractRule;

class WorkflowPlacesForDeletionAreUnoccupiedRule extends AbstractRule {
    /** @var ResourceRepository */
    private $resourceRepository;

    /** @var ResourceWorkflow */
    private $workflow;
    /** @var ResourceKindRepository */
    private $resourceKindRepository;

    public function __construct(ResourceRepository $resourceRepository, ResourceKindRepository $resourceKindRepository) {
        $this->resourceRepository = $resourceRepository;
        $this->resourceKindRepository = $resourceKindRepository;
    }

    public function forWorkflow(ResourceWorkflow $workflow): WorkflowPlacesForDeletionAreUnoccupiedRule {
        $instance = new self($this->resourceRepository, $this->resourceKindRepository);
        $instance->workflow = $workflow;
        return $instance;
    }

    public function validate($places) {
        Assertion::isArray($places);
        $workflowPlacesForDeletionIds =
            $this->getWorkflowPlacesForDeletion(
                $this->getWorkflowPlacesIds($this->workflow->getPlaces()),
                $this->getWorkflowPlacesIds($places)
            );
        if (empty($workflowPlacesForDeletionIds)) {
            return true;
        }
        $rkQuery = ResourceKindListQuery::builder()->filterByWorkflowId($this->workflow->getId())->build();
        $resourceKinds = $this->resourceKindRepository->findByQuery($rkQuery);
        $query = ResourceListQuery::builder()
            ->filterByWorkflowPlacesIds($workflowPlacesForDeletionIds)
            ->filterByResourceKinds($resourceKinds)
            ->build();
        return $this->resourceRepository
                ->findByQuery($query)
                ->getTotalCount() == 0;
    }

    private function getWorkflowPlacesIds($workflowPlaces): array {
        return array_map([$this, 'getWorkflowPlaceId'], $workflowPlaces);
    }

    private function getWorkflowPlacesForDeletion($currentPlaces, $updatedPlaces): array {
        return array_diff($currentPlaces, $updatedPlaces);
    }

    public function getWorkflowPlaceId($workflowPlace): string {
        return $workflowPlace instanceof ResourceWorkflowPlace
            ?
            $workflowPlace->getId()
            :
            $workflowPlace['id'];
    }
}
