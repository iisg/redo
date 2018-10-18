<?php
namespace Repeka\Domain\UseCase\Resource;

class ResourceEvaluateDisplayStrategiesFromDependenciesCommand extends ResourceEvaluateDisplayStrategiesCommand {
    private $evaluationDepth = 0;

    public function getEvaluationDepth(): int {
        return $this->evaluationDepth;
    }

    public function setEvaluationDepth(int $evaluationDepth): self {
        $this->evaluationDepth = $evaluationDepth;
        return $this;
    }

    public function getCommandClassName() {
        return ResourceEvaluateDisplayStrategiesCommand::class;
    }
}
