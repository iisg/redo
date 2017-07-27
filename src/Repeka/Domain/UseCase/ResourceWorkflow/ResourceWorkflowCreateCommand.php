<?php
namespace Repeka\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Cqrs\Command;

class ResourceWorkflowCreateCommand extends Command {
    private $name;
    private $places;
    private $transitions;
    private $diagram;
    private $thumbnail;
    private $resourceClass;

    public function __construct(array $name, array $places, array $transitions, string $resourceClass, $diagram, $thumbnail) {
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

    public function getResourceClass(): string {
        return $this->resourceClass;
    }
}
