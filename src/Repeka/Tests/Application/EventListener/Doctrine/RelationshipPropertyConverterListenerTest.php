<?php
namespace Repeka\Tests\Application\EventListener\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Repeka\Application\EventListener\Doctrine\RelationshipPropertyConverterListener;
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
        $contents = [1 => [['value' => 2]]];
        $converted = $this->listener->convertRelationshipMetadata($contents);
        $this->assertSame($contents, $converted);
    }

    public function testConvertsRelationships() {
        $contents = [
            1 => [['value' => 2]],
            2 => [['value' => $this->createResourceMock(1)], ['value' => $this->createResourceMock(2)]],
        ];
        $converted = $this->listener->convertRelationshipMetadata($contents);
        $this->assertEquals([['value' => 1], ['value' => 2]], $converted[2]);
    }

    public function testDoesNotChangeOriginalContents() {
        $contents = [
            1 => [['value' => 2]],
            2 => [['value' => $this->createResourceMock(1)], ['value' => $this->createResourceMock(2)]],
        ];
        $this->listener->convertRelationshipMetadata($contents);
        $this->assertInstanceOf(\PHPUnit_Framework_MockObject_MockObject::class, $contents[2][0]['value']);
    }

    public function testLeavesSubmetadata() {
        $contents = [
            1 => [['value' => 2]],
            2 => [['value' => $this->createResourceMock(44), 'submetadata' => [3 => [['value' => 'a']]]]],
        ];
        $converted = $this->listener->convertRelationshipMetadata($contents);
        $this->assertEquals([['value' => 44, 'submetadata' => [3 => [['value' => 'a']]]]], $converted[2]);
    }

    public function testConvertsSubmetadata() {
        $contents = [
            1 => [['value' => 2]],
            2 => [['value' => $this->createResourceMock(44), 'submetadata' => [3 => [['value' => $this->createResourceMock(55)]]]]],
        ];
        $converted = $this->listener->convertRelationshipMetadata($contents);
        $this->assertEquals([['value' => 44, 'submetadata' => [3 => [['value' => 55]]]]], $converted[2]);
    }

    public function testConvertsSubmetadataEventIfParentIsNotRelationship() {
        $contents = [
            1 => [['value' => 2]],
            2 => [['value' => 123, 'submetadata' => [3 => [['value' => $this->createResourceMock(55)]]]]],
        ];
        $converted = $this->listener->convertRelationshipMetadata($contents);
        $this->assertEquals([['value' => 123, 'submetadata' => [3 => [['value' => 55]]]]], $converted[2]);
    }
}
