<?php
namespace Repeka\Tests\Domain\Entity\Workflow;

use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
use Repeka\Tests\Traits\StubsTrait;

class ResourceWorkflowPlaceTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    public function testFromArray() {
        $place = ResourceWorkflowPlace::fromArray(
            [
                'id' => 'B',
                'label' => ['PL' => 'AA'],
                'requiredMetadataIds' => [1, 2, 3],
            ]
        );
        $this->assertEquals('B', $place->getId());
        $this->assertEquals(['PL' => 'AA'], $place->getLabel());
        $this->assertEquals([1, 2, 3], $place->restrictingMetadataIds()->all()->get());
    }

    public function testFromArrayWithMinimalFields() {
        $place = ResourceWorkflowPlace::fromArray(
            [
                'label' => ['PL' => 'AA'],
            ]
        );
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
            'autoAssignMetadataIds' => [6],
            'pluginsConfig' => [],
        ];
        $this->assertEquals($array, ResourceWorkflowPlace::fromArray($array)->toArray());
    }

    public function testGeneratingIdFromLabel() {
        $place = new ResourceWorkflowPlace(['EN' => 'Some cool place']);
        $this->assertEquals('some-cool-place', $place->getId());
    }

    public function testPlacesWithoutMetadataRequirementsAreEnterable() {
        $place = ResourceWorkflowPlace::fromArray(['label' => [], 'requiredMetadataIds' => []]);
        $rk = $this->createMock(ResourceKind::class);
        $rk->method('getMetadataIds')->willReturn([1]);
        $resource1 = $this->createResourceMock(1, $rk, []);
        $resource2 = $this->createResourceMock(1, $rk, [1 => null]);
        $this->assertEmpty($place->getMissingRequiredMetadataIds($resource1->getContents(), $rk));
        $this->assertEmpty($place->getMissingRequiredMetadataIds($resource2->getContents(), $rk));
    }

    public function testCheckingForRequiredMetadata() {
        $place = ResourceWorkflowPlace::fromArray(['label' => [], 'requiredMetadataIds' => [1, 2]]);
        $rk = $this->createMock(ResourceKind::class);
        $rk->method('getMetadataIds')->willReturn([1, 2, 3]);
        $resource1 = $this->createResourceMock(1, $rk, []);
        $resource2 = $this->createResourceMock(1, $rk, [1 => null]);
        $resource3 = $this->createResourceMock(1, $rk, [1 => null, 2 => null]);
        $resource4 = $this->createResourceMock(1, $rk, [1 => null, 2 => null, 3 => null]);
        $this->assertNotEmpty($place->getMissingRequiredMetadataIds($resource1->getContents(), $rk));
        $this->assertNotEmpty($place->getMissingRequiredMetadataIds($resource2->getContents(), $rk));
        $this->assertEmpty($place->getMissingRequiredMetadataIds($resource3->getContents(), $rk));
        $this->assertEmpty($place->getMissingRequiredMetadataIds($resource4->getContents(), $rk));
    }

    public function testGettingMissingRequiredMetadataIds() {
        $place = ResourceWorkflowPlace::fromArray(['label' => [], 'requiredMetadataIds' => [1, 2]]);
        $resourceContents = ResourceContents::empty();
        $rk = $this->createMock(ResourceKind::class);
        $rk->method('getMetadataIds')->willReturn([1, 2]);
        $this->assertEquals([1, 2], $place->getMissingRequiredMetadataIds($resourceContents, $rk));
        $resourceContents = $resourceContents->withMergedValues(1, null);
        $this->assertEquals([2], $place->getMissingRequiredMetadataIds($resourceContents, $rk));
    }

    public function testGettingMissingRequiredMetadataIdsWhenValueIsDefinedButEmpty() {
        $place = ResourceWorkflowPlace::fromArray(['label' => [], 'requiredMetadataIds' => [1, 2]]);
        $rk = $this->createMock(ResourceKind::class);
        $rk->method('getMetadataIds')->willReturn([1, 2]);
        $resourceContents = ResourceContents::fromArray([1 => []]);
        $this->assertEquals([1, 2], $place->getMissingRequiredMetadataIds($resourceContents, $rk));
    }

    public function testIgnoringMetadataNotInResourceKind() {
        $place = ResourceWorkflowPlace::fromArray(['label' => [], 'requiredMetadataIds' => [1, 2]]);
        $rk = $this->createMock(ResourceKind::class);
        $rk->method('getMetadataIds')->willReturn([1]);
        $resourceContents = ResourceContents::fromArray([1 => []]);
        $this->assertEquals([1], $place->getMissingRequiredMetadataIds($resourceContents, $rk));
    }
}
