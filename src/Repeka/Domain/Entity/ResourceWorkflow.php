<?php
namespace Repeka\Domain\Entity;

use Assert\Assertion;
use Repeka\Domain\Exception\ResourceWorkflow\NoSuchTransitionException;
use Repeka\Domain\Workflow\ResourceWorkflowDriver;

class ResourceWorkflow implements Identifiable {
    private $id;
    private $name;
    private $places = [];
    private $transitions = [];
    private $diagram;
    private $thumbnail;

    /** @var ResourceWorkflowDriver */
    private $workflow;

    public function __construct(array $name, array $places, array $transitions, $diagram = null, $thumbnail = null) {
        $this->update($name, $places, $transitions, $diagram, $thumbnail);
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
            $availablePlaceIds = $this->getWorkflowDriver()->getPlaces($resource);
            return EntityHelper::filterByIds($availablePlaceIds, $this->getPlacesAsObjects());
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
            $availableTransitionIds = $this->getWorkflowDriver()->getTransitions($resource);
            return EntityHelper::filterByIds($availableTransitionIds, $this->getTransitionsAsObjects());
        } else {
            return $this->getTransitionsAsObjects();
        }
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

    public function update(array $name, array $places, array $transitions, $diagram = null, $thumbnail = null) {
        $this->name = $name;
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
}
