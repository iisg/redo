<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommand;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommandAuditor;

class ResourceCreateCommandAuditorTest extends \PHPUnit_Framework_TestCase {
    public function testAuditsAfterCreation() {
        $auditor = new ResourceCreateCommandAuditor();
        $command = $this->createMock(ResourceCreateCommand::class);
        $this->assertNull($auditor->beforeHandling($command));
        $this->assertNull($auditor->afterError($command, new \Exception()));
        $resource = $this->createMock(ResourceEntity::class);
        $resource->method('getAuditData')->willReturn(['a']);
        $this->assertEquals(['a'], $auditor->afterHandling($command, $resource));
    }
}
