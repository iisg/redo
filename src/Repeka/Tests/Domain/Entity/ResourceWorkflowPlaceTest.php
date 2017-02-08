<?php
namespace Repeka\Tests\Domain\Entity;

use Repeka\Domain\Entity\ResourceWorkflowPlace;

class ResourceWorkflowPlaceTest extends \PHPUnit_Framework_TestCase {
    public function testFromArray() {
        $place = ResourceWorkflowPlace::fromArray([
            'id' => 'B',
            'label' => ['PL' => 'AA']
        ]);
        $this->assertEquals('B', $place->getId());
        $this->assertEquals(['PL' => 'AA'], $place->getLabel());
    }

    public function testToArray() {
        $array = [
            'id' => 'B',
            'label' => ['PL' => 'AA']
        ];
        $this->assertEquals($array, ResourceWorkflowPlace::fromArray($array)->toArray());
    }

    public function testGeneratingIdFromLabel() {
        $place = new ResourceWorkflowPlace(['EN' => 'Some cool place']);
        $this->assertEquals('some-cool-place', $place->getId());
    }
}
