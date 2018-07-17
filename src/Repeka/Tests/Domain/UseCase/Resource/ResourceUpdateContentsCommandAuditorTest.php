<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\UseCase\Resource\ResourceUpdateContentsCommand;
use Repeka\Domain\UseCase\Resource\ResourceUpdateContentsCommandAuditor;

class ResourceUpdateContentsCommandAuditorTest extends \PHPUnit_Framework_TestCase {
    public function testJoinsBeforeWithAfter() {
        $auditor = new ResourceUpdateContentsCommandAuditor();
        /** @var ResourceUpdateContentsCommand|\PHPUnit_Framework_MockObject_MockObject $command */
        $command = $this->createMock(ResourceUpdateContentsCommand::class);
        /** @var ResourceEntity|\PHPUnit_Framework_MockObject_MockObject $resource */
        $resource = $this->createMock(ResourceEntity::class);
        $resource->method('getAuditData')->willReturnOnConsecutiveCalls(['bef'], ['aft']);
        $command->method('getResource')->willReturn($resource);
        $beforeHandlingResult = $auditor->beforeHandling($command);
        $this->assertEquals(['before' => ['bef']], $beforeHandlingResult);
        $this->assertEquals(['before'=> ['bef'], 'after' => ['aft']], $auditor->afterHandling($command, $resource, $beforeHandlingResult));
    }
}
