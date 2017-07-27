<?php
namespace Repeka\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Cqrs\Command;

class ResourceWorkflowSimulateCommand extends Command {
    private $places;
    private $transitions;
    private $transitionId;
    private $currentPlaces;

    public function __construct(
        array $places,
        array $transitions,
        array $currentPlaces = [],
        string $transitionId = ''
    ) {
        $this->places = $places;
        $this->transitions = $transitions;
        $this->transitionId = $transitionId;
        $this->currentPlaces = $currentPlaces;
    }

    public function getPlaces(): array {
        return $this->places;
    }

    public function getTransitions(): array {
        return $this->transitions;
    }

    public function getTransitionId(): string {
        return $this->transitionId;
    }

    public function getCurrentPlaces(): array {
        return $this->currentPlaces;
    }
}
