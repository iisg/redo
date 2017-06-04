<?php
namespace Repeka\Tests\Domain\Entity;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceWorkflowPlace;

class ResourceWorkflowPlaceTest extends \PHPUnit_Framework_TestCase {
    public function testFromArray() {
        $place = ResourceWorkflowPlace::fromArray([
            'id' => 'B',
            'label' => ['PL' => 'AA'],
            'requiredMetadataIds' => [1, 2, 3]
        ]);
        $this->assertEquals('B', $place->getId());
        $this->assertEquals(['PL' => 'AA'], $place->getLabel());
        $this->assertEquals([1, 2, 3], $place->getRequiredMetadataIds());
    }

    public function testFromArrayWithMinimalFields() {
        $place = ResourceWorkflowPlace::fromArray([
            'label' => ['PL' => 'AA'],
        ]);
        $this->assertEquals(['PL' => 'AA'], $place->getLabel());
        $this->assertEquals([], $place->getRequiredMetadataIds());
    }

    public function testToArray() {
        $array = [
            'id' => 'B',
            'label' => ['PL' => 'AA'],
            'requiredMetadataIds' => [1, 2, 3]
        ];
        $this->assertEquals($array, ResourceWorkflowPlace::fromArray($array)->toArray());
    }

    public function testGeneratingIdFromLabel() {
        $place = new ResourceWorkflowPlace(['EN' => 'Some cool place']);
        $this->assertEquals('some-cool-place', $place->getId());
    }

    public function testCanEnterStateIfRequiredMetadataPresent() {
        $resource = $this->createMock(ResourceEntity::class);
        $resource->method('getContents')->willReturn([1 => ['a'], 2 => ['a'], 3 => ['a']]);
        $place = ResourceWorkflowPlace::fromArray(['label' => [], 'requiredMetadataIds' => [1, 2, 3]]);
        $this->assertTrue($place->isRequiredMetadataFilled($resource));
    }

    public function testCanNotEnterStateIfRequiredMetadataIsMissing() {
        $resource = $this->createMock(ResourceEntity::class);
        $resource->method('getContents')->willReturn([1 => ['a'], 3 => ['a']]);
        $place = ResourceWorkflowPlace::fromArray(['label' => [], 'requiredMetadataIds' => [1, 2, 3]]);
        $this->assertFalse($place->isRequiredMetadataFilled($resource));
    }
}
