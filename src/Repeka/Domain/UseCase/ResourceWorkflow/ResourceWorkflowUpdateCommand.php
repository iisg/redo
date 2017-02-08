<?php
namespace Repeka\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\ResourceWorkflow;

class ResourceWorkflowUpdateCommand extends Command {
    /** @var ResourceWorkflow */
    private $workflow;
    private $places;
    private $transitions;
    private $diagram;
    private $thumbnail;

    public function __construct(ResourceWorkflow $workflow, array $places, array $transitions, $diagram, $thumbnail) {
        $this->workflow = $workflow;
        $this->places = $places;
        $this->transitions = $transitions;
        $this->diagram = $diagram;
        $this->thumbnail = $thumbnail;
    }

    public function getWorkflow(): ResourceWorkflow {
        return $this->workflow;
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
