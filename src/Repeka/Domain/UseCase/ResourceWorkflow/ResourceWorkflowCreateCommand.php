<?php
namespace Repeka\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Cqrs\ResourceClassAwareCommand;

class ResourceWorkflowCreateCommand extends ResourceClassAwareCommand {
    private $name;
    private $places;
    private $transitions;
    private $diagram;
    private $thumbnail;

    public function __construct(array $name, array $places, array $transitions, string $resourceClass, $diagram, $thumbnail) {
        parent::__construct($resourceClass);
        $this->name = $name;
        $this->places = $places;
        $this->transitions = $transitions;
        $this->diagram = $diagram;
        $this->thumbnail = $thumbnail;
        $this->resourceClass = $resourceClass;
    }

    public function getName(): array {
        return $this->name;
    }

    public function getPlaces(): array {
        return $this->places;
    }

    public function getTransitions(): array {
        return $this->transitions;
    }

    public function getDiagram() {
        return $this->diagram;
    }

    public function getThumbnail() {
        return $this->thumbnail;
    }
}
