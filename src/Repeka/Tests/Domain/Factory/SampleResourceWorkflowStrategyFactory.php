<?php
namespace Repeka\Tests\Domain\Factory;

use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Factory\ResourceWorkflowStrategy;
use Repeka\Domain\Factory\ResourceWorkflowStrategyFactory;

class SampleResourceWorkflowStrategyFactory extends ResourceWorkflowStrategyFactory {
    public $strategyMock;

    public function __construct($strategyMock) {
        $this->strategyMock = $strategyMock;
    }

    protected function createStrategy(ResourceWorkflow $resourceWorkflow): ResourceWorkflowStrategy {
        return $this->strategyMock;
    }
}
