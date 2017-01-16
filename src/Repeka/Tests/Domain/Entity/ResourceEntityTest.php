<?php
namespace Repeka\Tests\Domain\Entity;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;

class ResourceEntityTest extends \PHPUnit_Framework_TestCase {
    public function testUpdatingContents() {
        $resource = new ResourceEntity($this->createMock(ResourceKind::class), [1 => 'AA']);
        $resource->updateContents([2 => 'AA']);
        $this->assertEquals([2 => 'AA'], $resource->getContents());
    }
}
