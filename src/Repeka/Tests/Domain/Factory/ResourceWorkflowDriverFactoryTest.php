<?php
namespace Repeka\Tests\Domain\Factory;

use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Factory\ResourceWorkflowDriver;

class ResourceWorkflowDriverFactoryTest extends \PHPUnit_Framework_TestCase {
    /** @var SampleResourceWorkflowDriverFactory */
    private $driverFactory;

    protected function setUp() {
        $this->driverFactory = new SampleResourceWorkflowDriverFactory($this->createMock(ResourceWorkflowDriver::class));
    }

    public function testSettingDriver() {
        $workflow = $this->createMock(ResourceWorkflow::class);
        $workflow->expects($this->once())->method('setWorkflowDriver')->with($this->driverFactory->driverMock);
        $this->driverFactory->setForWorkflow($workflow);
    }
}
