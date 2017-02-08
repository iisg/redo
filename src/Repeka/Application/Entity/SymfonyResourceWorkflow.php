<?php
namespace Repeka\Application\Entity;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceWorkflow;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;

class SymfonyResourceWorkflow implements ResourceWorkflow {
    /** @var Workflow */
    private $workflow;

    public function __construct(Workflow $workflow) {
        $this->workflow = $workflow;
    }

    public function getCurrentMarking(ResourceEntity $resource): string {
        $marking = $this->workflow->getMarking($resource);
        $firstMarkingName = key($marking->getPlaces());
        return $firstMarkingName;
    }

    /** @return string[] */
    public function getEnabledTransitions(ResourceEntity $resource): array {
        return array_map(function (Transition $transition) {
            return $transition->getName();
        }, $this->workflow->getEnabledTransitions($resource));
    }

    public function apply(ResourceEntity $resource, string $transition): ResourceEntity {
        $this->workflow->apply($resource, $transition);
        return $resource;
    }
}
