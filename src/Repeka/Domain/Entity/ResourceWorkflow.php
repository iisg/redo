<?php
namespace Repeka\Domain\Entity;

use Assert\Assertion;
use Repeka\Domain\Exception\ResourceWorkflow\NoSuchTransitionException;
use Repeka\Domain\Workflow\ResourceWorkflowConditionalTransitionTrait;
use Repeka\Domain\Workflow\ResourceWorkflowDriver;
use Repeka\Domain\Workflow\UnsatisfiedTransitionExplanation;

class ResourceWorkflow {
    private $id;
    private $name;
    private $places = [];
    private $transitions = [];
    private $diagram;
    private $thumbnail;

    /** @var ResourceWorkflowDriver */
    private $workflow;

    public function __construct(array $name) {
        $this->name = $name;
    }

    public function getId() {
        return $this->id;
    }

    public function getName(): array {
        return $this->name;
    }

    /** @return ResourceWorkflowPlace[] */
    public function getPlaces(ResourceEntity $resource = null): array {
        if ($resource) {
            $available = $this->getWorkflowDriver()->getPlaces($resource);
            return $this->filterByIds($available, $this->getPlacesAsObjects());
        } else {
            return $this->getPlacesAsObjects();
        }
    }

    public function getInitialPlace(): ResourceWorkflowPlace {
        return $this->getPlaces()[0];
    }

    /** @return ResourceWorkflowTransition[] */
    public function getTransitions(ResourceEntity $resource = null): array {
        if ($resource) {
            $available = $this->getWorkflowDriver()->getTransitions($resource);
            return $this->filterByIds($available, $this->getTransitionsAsObjects());
        } else {
            return $this->getTransitionsAsObjects();
        }
    }

    public function isTransitionPossible(string $transitionId, ResourceEntity $resource, User $executor): bool {
        /** @var ResourceWorkflowTransition $transition */
        $transition = $this->getTransition($transitionId);
        $explanation = new UnsatisfiedTransitionExplanation($this, $transition, $resource, $executor);
        return $explanation->isSatisfied();
    }

    private function filterByIds(array $idsToLeave, array $objects): array {
        return array_values(array_filter($objects, function ($objectWithId) use ($idsToLeave) {
            return in_array($objectWithId->getId(), $idsToLeave);
        }));
    }

    /** @return ResourceWorkflowTransition[] */
    private function getTransitionsAsObjects(): array {
        return array_map(function (array $transition) {
            return ResourceWorkflowTransition::fromArray($transition);
        }, $this->transitions);
    }

    private function getPlacesAsObjects() {
        return array_map(function (array $place) {
            return ResourceWorkflowPlace::fromArray($place);
        }, $this->places);
    }

    public function apply(ResourceEntity $resource, string $transition): ResourceEntity {
        return $this->getWorkflowDriver()->apply($resource, $transition);
    }

    public function setCurrentPlaces(ResourceEntity $resourceEntity, array $places): ResourceEntity {
        return $this->getWorkflowDriver()->setCurrentPlaces($resourceEntity, $places);
    }

    public function getDiagram() {
        return $this->diagram;
    }

    public function getThumbnail() {
        return $this->thumbnail;
    }

    public function update(array $places, array $transitions, $diagram = null, $thumbnail = null) {
        $this->places = [];
        foreach ($places as $place) {
            if (!$place instanceof ResourceWorkflowPlace) {
                $place = ResourceWorkflowPlace::fromArray($place);
            }
            $this->addPlace($place);
        }
        $this->transitions = [];
        foreach ($transitions as $transition) {
            if (!$transition instanceof ResourceWorkflowTransition) {
                $transition = ResourceWorkflowTransition::fromArray($transition);
            }
            $this->addTransition($transition);
        }
        $this->diagram = $diagram;
        $this->thumbnail = $thumbnail;
    }

    private function addPlace(ResourceWorkflowPlace $place) {
        $this->places[] = $place->toArray();
    }

    private function addTransition(ResourceWorkflowTransition $transition) {
        $this->transitions[] = $transition->toArray();
    }

    public function setWorkflowDriver(ResourceWorkflowDriver $driver) {
        $this->workflow = $driver;
    }

    private function getWorkflowDriver(): ResourceWorkflowDriver {
        Assertion::notNull($this->workflow, 'ResourceWorkflowDriver has not been set. You need to use ResourceWorkflowDriverFactory.');
        return $this->workflow;
    }

    public function getTransition($transitionId): ResourceWorkflowTransition {
        foreach ($this->getTransitionsAsObjects() as $transition) {
            if ($transition->getId() == $transitionId) {
                return $transition;
            }
        }
        throw new NoSuchTransitionException($transitionId, $this);
    }

    /** @return UnsatisfiedTransitionExplanation[] */
    public function getUnsatisfiedTransitionExplanations(ResourceEntity $resource, User $user): array {
        $transitions = $this->getTransitions($resource);
        $unsatisfiedTransitions = [];
        foreach ($transitions as $transition) {
            $explanation = new UnsatisfiedTransitionExplanation($this, $transition, $resource, $user);
            if (!$explanation->isSatisfied()) {
                $unsatisfiedTransitions[$transition->getId()] = $explanation;
            }
        }
        return $unsatisfiedTransitions;
    }
}
