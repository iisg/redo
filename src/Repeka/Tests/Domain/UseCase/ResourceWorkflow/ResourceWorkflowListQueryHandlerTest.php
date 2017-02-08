<?php
namespace Repeka\Tests\Domain\UseCase\ResourceWorkflow;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\ResourceWorkflowRepository;
use Repeka\Domain\UseCase\ResourceWorkflow\ResourceWorkflowListQueryHandler;

class ResourceWorkflowListQueryHandlerTest extends \PHPUnit_Framework_TestCase {
    /** @var PHPUnit_Framework_MockObject_MockObject|ResourceWorkflowRepository */
    private $workflowRepository;
    /** @var ResourceWorkflowListQueryHandler */
    private $handler;

    protected function setUp() {
        $this->workflowRepository = $this->createMock(ResourceWorkflowRepository::class);
        $this->handler = new ResourceWorkflowListQueryHandler($this->workflowRepository);
    }

    public function testGettingTheList() {
        $resources = [$this->createMock(ResourceEntity::class)];
        $this->workflowRepository->expects($this->once())->method('findAll')->willReturn($resources);
        $returnedList = $this->handler->handle();
        $this->assertSame($resources, $returnedList);
    }
}
