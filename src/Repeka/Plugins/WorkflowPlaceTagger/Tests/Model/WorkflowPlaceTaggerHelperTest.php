<?php
namespace Repeka\Plugins\WorkflowPlaceTagger\Tests\Model;

use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
use Repeka\Domain\Workflow\ResourceWorkflowPlugin;
use Repeka\Plugins\WorkflowPlaceTagger\Model\WorkflowPlaceTaggerHelper;
use Repeka\Plugins\WorkflowPlaceTagger\Model\WorkflowPlaceTaggerResourceWorkflowPlugin;
use Repeka\Tests\Traits\StubsTrait;

class WorkflowPlaceTaggerHelperTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceWorkflow|\PHPUnit_Framework_MockObject_MockObject */
    private $workflow;
    /** @var WorkflowPlaceTaggerHelper */
    private $helper;

    /** @before */
    public function init() {
        $placeA = $this->createPlaceWithTags('A', ['X' => '1', 'Y' => '2']);
        $placeB = $this->createPlaceWithTags('A', ['Y' => ['1', '2']]);
        $this->workflow = $this->createMock(ResourceWorkflow::class);
        $this->workflow->method('getPlaces')->willReturn([$placeA, $placeB]);
        $this->helper = new WorkflowPlaceTaggerHelper();
    }

    private function createPlaceWithTags(string $label, array $tags): ResourceWorkflowPlace {
        $pluginsConfig = [];
        foreach ($tags as $tagName => $tagValues) {
            if (!is_array($tagValues)) {
                $tagValues = [$tagValues];
            }
            foreach ($tagValues as $tagValue) {
                $pluginsConfig[] = [
                    'name' => ResourceWorkflowPlugin::getNameFromClassName(WorkflowPlaceTaggerResourceWorkflowPlugin::class),
                    'config' => ['tagName' => $tagName, 'tagValue' => $tagValue],
                ];
            }
        }
        return new ResourceWorkflowPlace(['PL' => $label], null, [], [], [], [], $pluginsConfig);
    }

    public function testGetTaggedPlaces() {
        $places = $this->helper->getTaggedPlaces('X', $this->workflow);
        $this->assertCount(1, $places);
        $this->assertEquals('A', $places[0]['place']->getLabel()['PL']);
        return $places;
    }

    public function testGetTaggedPlacesForInvalidTag() {
        $places = $this->helper->getTaggedPlaces('U', $this->workflow);
        $this->assertEmpty($places);
    }

    public function testGetTaggedPlacesFromResourceKind() {
        $rk = $this->createResourceKindMock(1, 'books', [], $this->workflow);
        $places = $this->helper->getTaggedPlaces('X', $rk);
        $this->assertNotEmpty($places);
        return $rk;
    }

    /** @depends testGetTaggedPlacesFromResourceKind */
    public function testGetTaggedPlacesFromResource(ResourceKind $resourceKind) {
        $resource = $this->createResourceMock(1, $resourceKind);
        $places = $this->helper->getTaggedPlaces('X', $resource);
        $this->assertNotEmpty($places);
    }

    public function testGetTaggedPlacesFromNull() {
        $places = $this->helper->getTaggedPlaces('X', null);
        $this->assertEmpty($places);
    }

    /** @depends testGetTaggedPlaces */
    public function testGetTagValue(array $places) {
        $firstPlace = $places[0];
        $this->assertArrayHasKey('tagValues', $firstPlace);
        $this->assertArrayHasKey('place', $firstPlace);
        $this->assertEquals(['1'], $firstPlace['tagValues']);
    }

    public function testGetTagValuesFromMultiplePlaces() {
        $places = $this->helper->getTaggedPlaces('Y', $this->workflow);
        $this->assertCount(2, $places);
        $this->assertEquals(['2'], $places[0]['tagValues']);
        $this->assertEquals(['1', '2'], $places[1]['tagValues']);
    }
}
