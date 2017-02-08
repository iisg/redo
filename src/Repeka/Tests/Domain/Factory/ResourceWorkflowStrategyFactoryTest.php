<?php
namespace Repeka\Tests\Domain\Factory;

use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Factory\ResourceWorkflowStrategy;

class ResourceWorkflowStrategyFactoryTest extends \PHPUnit_Framework_TestCase {
    /** @var SampleResourceWorkflowStrategyFactory */
    private $strategyFactory;

    protected function setUp() {
        $this->strategyFactory = new SampleResourceWorkflowStrategyFactory($this->createMock(ResourceWorkflowStrategy::class));
    }

    public function testSettingStrategy() {
        $workflow = $this->createMock(ResourceWorkflow::class);
        $workflow->expects($this->once())->method('setWorkflowStrategy')->with($this->strategyFactory->strategyMock);
        $this->strategyFactory->setForWorkflow($workflow);
    }
}
