<?php
namespace Repeka\Plugins\WorkflowPlaceTagger\Model;

use Assert\Assertion;
use Repeka\Domain\Entity\Identifiable;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowTransition;
use Repeka\Domain\Utils\EntityUtils;

class WorkflowPlaceTaggedPath {
    /** @var string */
    private $tagName;
    /** @var ResourceWorkflow */
    private $workflow;
    /** @var WorkflowPlaceTaggerHelper */
    private $workflowPlaceTaggerHelper;
    /** @var @array */
    private $path;

    public function __construct(string $tagName, ResourceWorkflow $workflow, WorkflowPlaceTaggerHelper $workflowPlaceTaggerHelper) {
        $this->tagName = $tagName;
        $this->workflow = $workflow;
        $this->workflowPlaceTaggerHelper = $workflowPlaceTaggerHelper;
        $this->buildPath();
    }

    private function buildPath() {
        $startAndEnd = $this->workflowPlaceTaggerHelper->getTaggedPlaces($this->tagName, $this->workflow);
        Assertion::count(
            $startAndEnd,
            2,
            sprintf(
                "Invalid path tags - there should be 2 places marked with %s to create a path in  \"%d\" Worklow, %d found.",
                $this->tagName,
                $this->workflow->getId(),
                count($startAndEnd)
            )
        );
        /** @var ResourceWorkflowPlace $startPlace */
        $startPlace = $startAndEnd[0]['tagValues'][0] == 'start' ? $startAndEnd[0]['place'] : $startAndEnd[1]['place'];
        /** @var ResourceWorkflowPlace $endPlace */
        $endPlace = $startAndEnd[0]['tagValues'][0] == 'start' ? $startAndEnd[1]['place'] : $startAndEnd[0]['place'];
        $path = [$startPlace];
        $currentPlace = $startPlace;
        while ($currentPlace->getId() != $endPlace->getId()) {
            $transitions = $this->workflow->getTransitionsFromPlace($currentPlace);
            Assertion::count(
                $transitions,
                1,
                "Expected to found 1 transition from the place {$currentPlace->getId()}, " . count($transitions) . " found"
            );
            $transition = $transitions[0];
            $tos = $transition->getToIds();
            Assertion::count($tos, 1, "Expected to found 1 target place for the transition {$transition->getId()}, %d found");
            $currentPlace = $this->workflow->getPlace($tos[0]);
            $path[] = $transition;
            $path[] = $currentPlace;
        }
        $this->path = $path;
    }

    /** @return ResourceWorkflowPlace[] */
    public function getPlaces(): array {
        return array_values(
            array_filter(
                $this->path,
                function ($index) {
                    return $index % 2 === 0;
                },
                ARRAY_FILTER_USE_KEY
            )
        );
    }

    /** @return ResourceWorkflowTransition[] */
    public function getTransitions(): array {
        return array_values(
            array_filter(
                $this->path,
                function ($index) {
                    return $index % 2 !== 0;
                },
                ARRAY_FILTER_USE_KEY
            )
        );
    }

    public function hasTransition($transitionOrId) {
        $transitionId = $transitionOrId instanceof ResourceWorkflowTransition ? $transitionOrId->getId() : $transitionOrId;
        return in_array($transitionId, EntityUtils::mapToIds($this->getTransitions()));
    }

    public function isBefore(string $elementIdA, string $elementIdB): bool {
        $pathIds = EntityUtils::mapToIds($this->path);
        Assertion::inArray($elementIdA, $pathIds, "Element $elementIdA is not in path.");
        Assertion::inArray($elementIdB, $pathIds, "Element $elementIdB is not in path.");
        return array_search($elementIdA, $pathIds) < array_search($elementIdB, $pathIds);
    }

    public function getLastPlace(): ResourceWorkflowPlace {
        $places = $this->getPlaces();
        return end($places);
    }

    public function getLastTransition(): ResourceWorkflowTransition {
        $transitions = $this->getTransitions();
        return end($transitions);
    }

    public function contains($id): bool {
        return in_array($id, EntityUtils::mapToIds($this->path));
    }

    /** @return ResourceWorkflowPlace|ResourceWorkflowTransition */
    public function getNext($id): Identifiable {
        $index = array_search($id, EntityUtils::mapToIds($this->path));
        Assertion::integer($index, "Element $id not found in the path.");
        return $this->path[$index + 1];
    }
}
