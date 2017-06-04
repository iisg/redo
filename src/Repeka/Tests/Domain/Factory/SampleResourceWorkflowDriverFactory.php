<?php
namespace Repeka\Tests\Domain\Factory;

use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Factory\ResourceWorkflowDriverFactory;
use Repeka\Domain\Workflow\ResourceWorkflowDriver;

class SampleResourceWorkflowDriverFactory extends ResourceWorkflowDriverFactory {
    public $driverMock;

    public function __construct($driverMock) {
        $this->driverMock = $driverMock;
    }

    protected function createDriver(ResourceWorkflow $resourceWorkflow): ResourceWorkflowDriver {
        return $this->driverMock;
    }
}
