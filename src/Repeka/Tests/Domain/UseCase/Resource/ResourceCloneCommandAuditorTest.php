<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\UseCase\Resource\ResourceCloneCommand;
use Repeka\Domain\UseCase\Resource\ResourceCloneCommandAuditor;

class ResourceCloneCommandAuditorTest extends \PHPUnit_Framework_TestCase {
    public function testAuditsAfterCreation() {
        $auditor = new ResourceCloneCommandAuditor();
        /** @var ResourceCloneCommand|\PHPUnit_Framework_MockObject_MockObject $command */
        $command = $this->createMock(ResourceCloneCommand::class);
        /** @var ResourceEntity|\PHPUnit_Framework_MockObject_MockObject $resource */
        $resource = $this->createMock(ResourceEntity::class);
        $resource->method('getAuditData')->willReturn(['a']);
        $this->assertNull($auditor->beforeHandling($command));
        $this->assertEquals(['after' => ['a']], $auditor->afterHandling($command, $resource, null));
    }
}
