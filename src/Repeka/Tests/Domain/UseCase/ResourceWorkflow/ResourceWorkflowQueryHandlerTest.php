<?php
namespace Repeka\Tests\Domain\UseCase\ResourceWorkflow;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Repository\ResourceWorkflowRepository;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowQuery;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowQueryHandler;

class ResourceWorkflowQueryHandlerTest extends \PHPUnit_Framework_TestCase {
    /** @var PHPUnit_Framework_MockObject_MockObject|ResourceWorkflowRepository */
    private $workflowRepository;

    /** @var ResourceWorkflowQueryHandler */
    private $handler;

    protected function setUp() {
        $this->workflowRepository = $this->createMock(ResourceWorkflowRepository::class);
        $this->handler = new ResourceWorkflowQueryHandler($this->workflowRepository);
    }

    public function testHandling() {
        $this->workflowRepository->expects($this->once())->method('findOne')->with(2);
        $this->handler->handle(new ResourceWorkflowQuery(2));
    }
}
