<?php
namespace Repeka\Tests\Application\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Repeka\Application\EventListener\Doctrine\RelationshipPropertyConverterListener;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Tests\Traits\StubsTrait;

class RelationshipPropertyConverterListenerTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var RelationshipPropertyConverterListener */
    private $listener;

    /** @before */
    public function init() {
        $this->listener = new RelationshipPropertyConverterListener();
    }

    private function createEvent($entity): LifecycleEventArgs {
        $event = $this->createMock(LifecycleEventArgs::class);
        $event->method('getEntity')->willReturn($entity);
        return $event;
    }

    public function testDoesNotFailWithUnknownEntity() {
        $this->listener->prePersist($this->createEvent(new \stdClass()));
    }

    public function testDoesNothingForResourceWithoutRelationshipValues() {
        $contents = ResourceContents::fromArray([1 => [['value' => 2]]]);
        $resource = $this->createResourceMock(1, null, $contents);
        $resource->expects($this->once())->method('updateContents')->with($contents);
        $this->listener->convertResourceContents($resource);
    }

    public function testConvertsRelationships() {
        $resource = $this->createResourceMock(1, null, [
            1 => [['value' => 2]],
            2 => [['value' => $this->createResourceMock(1)], ['value' => $this->createResourceMock(2)]],
        ]);
        $resource->expects($this->once())->method('updateContents')->willReturnCallback(function (ResourceContents $converted) {
            $this->assertEquals([['value' => 1], ['value' => 2]], $converted[2]);
        });
        $this->listener->convertResourceContents($resource);
    }

    public function testLeavesSubmetadata() {
        $resource = $this->createResourceMock(1, null, [
            1 => [['value' => 2]],
            2 => [['value' => $this->createResourceMock(44), 'submetadata' => [3 => [['value' => 'a']]]]],
        ]);
        $resource->expects($this->once())->method('updateContents')->willReturnCallback(function (ResourceContents $converted) {
            $this->assertEquals([['value' => 44, 'submetadata' => [3 => [['value' => 'a']]]]], $converted[2]);
        });
        $this->listener->convertResourceContents($resource);
    }

    public function testConvertsSubmetadata() {
        $resource = $this->createResourceMock(1, null, [
            1 => [['value' => 2]],
            2 => [['value' => $this->createResourceMock(44), 'submetadata' => [3 => [['value' => $this->createResourceMock(55)]]]]],
        ]);
        $resource->expects($this->once())->method('updateContents')->willReturnCallback(function (ResourceContents $converted) {
            $this->assertEquals([['value' => 44, 'submetadata' => [3 => [['value' => 55]]]]], $converted[2]);
        });
        $this->listener->convertResourceContents($resource);
    }

    public function testConvertsSubmetadataEventIfParentIsNotRelationship() {
        $resource = $this->createResourceMock(1, null, [
            1 => [['value' => 2]],
            2 => [['value' => 123, 'submetadata' => [3 => [['value' => $this->createResourceMock(55)]]]]],
        ]);
        $resource->expects($this->once())->method('updateContents')->willReturnCallback(function (ResourceContents $converted) {
            $this->assertEquals([['value' => 123, 'submetadata' => [3 => [['value' => 55]]]]], $converted[2]);
        });
        $this->listener->convertResourceContents($resource);
    }
}
