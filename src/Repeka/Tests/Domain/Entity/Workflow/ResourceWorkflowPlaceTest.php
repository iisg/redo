<?php
namespace Repeka\Tests\Domain\Entity\Workflow;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;

class ResourceWorkflowPlaceTest extends \PHPUnit_Framework_TestCase {
    public function testFromArray() {
        $place = ResourceWorkflowPlace::fromArray([
            'id' => 'B',
            'label' => ['PL' => 'AA'],
            'requiredMetadataIds' => [1, 2, 3]
        ]);
        $this->assertEquals('B', $place->getId());
        $this->assertEquals(['PL' => 'AA'], $place->getLabel());
        $this->assertEquals([1, 2, 3], $place->restrictingMetadataIds()->all()->get());
    }

    public function testFromArrayWithMinimalFields() {
        $place = ResourceWorkflowPlace::fromArray([
            'label' => ['PL' => 'AA'],
        ]);
        $this->assertEquals(['PL' => 'AA'], $place->getLabel());
        $this->assertEquals([], $place->restrictingMetadataIds()->all()->get());
    }

    public function testToArray() {
        $array = [
            'id' => 'B',
            'label' => ['PL' => 'AA'],
            'requiredMetadataIds' => [1, 2, 3],
            'lockedMetadataIds' => [4, 5],
            'assigneeMetadataIds' => [4, 5],
        ];
        $this->assertEquals($array, ResourceWorkflowPlace::fromArray($array)->toArray());
    }

    public function testGeneratingIdFromLabel() {
        $place = new ResourceWorkflowPlace(['EN' => 'Some cool place']);
        $this->assertEquals('some-cool-place', $place->getId());
    }

    public function testPlacesWithoutMetadataRequirementsAreEnterable() {
        $place = ResourceWorkflowPlace::fromArray(['label' => [], 'requiredMetadataIds' => []]);
        $resource1 = $this->createMock(ResourceEntity::class);
        $resource1->method('getContents')->willReturn([]);
        $resource2 = $this->createMock(ResourceEntity::class);
        $resource2->method('getContents')->willReturn([1 => null]);
        $this->assertTrue($place->resourceHasRequiredMetadata($resource1));
        $this->assertTrue($place->resourceHasRequiredMetadata($resource2));
    }

    public function testCheckingForRequiredMetadata() {
        $place = ResourceWorkflowPlace::fromArray(['label' => [], 'requiredMetadataIds' => [1, 2]]);
        $resource1 = $this->createMock(ResourceEntity::class);
        $resource1->method('getContents')->willReturn([]);
        $resource2 = $this->createMock(ResourceEntity::class);
        $resource2->method('getContents')->willReturn([1 => null]);
        $resource3 = $this->createMock(ResourceEntity::class);
        $resource3->method('getContents')->willReturn([1 => null, 2 => null]);
        $resource4 = $this->createMock(ResourceEntity::class);
        $resource4->method('getContents')->willReturn([1 => null, 2 => null, 3 => null]);
        $this->assertFalse($place->resourceHasRequiredMetadata($resource1));
        $this->assertFalse($place->resourceHasRequiredMetadata($resource2));
        $this->assertTrue($place->resourceHasRequiredMetadata($resource3));
        $this->assertTrue($place->resourceHasRequiredMetadata($resource4));
    }

    public function testGettingMissingRequiredMetadataIds() {
        $place = ResourceWorkflowPlace::fromArray(['label' => [], 'requiredMetadataIds' => [1, 2]]);
        $resource1 = $this->createMock(ResourceEntity::class);
        $resource1->method('getContents')->willReturn([]);
        $resource2 = $this->createMock(ResourceEntity::class);
        $resource2->method('getContents')->willReturn([1 => null]);
        $this->assertEquals([1, 2], $place->getMissingRequiredMetadataIds($resource1));
        $this->assertEquals([2], $place->getMissingRequiredMetadataIds($resource2));
    }
}