<?php
namespace Repeka\Plugins\WorkflowPlaceTagger\Tests\Model;

use Repeka\Application\Factory\SymfonyResourceWorkflowDriverFactory;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
use Repeka\Domain\Utils\EntityUtils;
use Repeka\Domain\Workflow\ResourceWorkflowPlugin;
use Repeka\Plugins\WorkflowPlaceTagger\Model\WorkflowPlaceTaggedPath;
use Repeka\Plugins\WorkflowPlaceTagger\Model\WorkflowPlaceTaggerHelper;
use Repeka\Plugins\WorkflowPlaceTagger\Model\WorkflowPlaceTaggerResourceWorkflowPlugin;
use Repeka\Tests\Traits\StubsTrait;

class WorkflowPlaceTaggedPathTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceWorkflow|\PHPUnit_Framework_MockObject_MockObject */
    private $workflow;
    /** @var WorkflowPlaceTaggerHelper */
    private $helper;

    /** @before */
    public function init() {
        $places = [];
        foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $placeId) {
            $places[] = ['id' => $placeId, 'label' => ['PL' => $placeId]];
        }
        $transitions = [];
        foreach (['AB', 'BC', 'CD', 'DE', 'CF'] as $transitionId) {
            $transitions[] = [
                'id' => $transitionId,
                'froms' => [$transitionId{0}],
                'tos' => [$transitionId{1}],
                'label' => ['PL' => $transitionId],
            ];
        }
        $this->workflow = new ResourceWorkflow([], $places, $transitions, 'books');
        (new SymfonyResourceWorkflowDriverFactory())->setForWorkflow($this->workflow);
        $this->helper = new WorkflowPlaceTaggerHelper();
    }

    public function testErrorWhenNoTags() {
        $this->expectException(\InvalidArgumentException::class);
        new WorkflowPlaceTaggedPath('unicorn', $this->workflow, $this->helper);
    }

    public function testGetPath() {
        $this->tagPlace('A', 'unicorn', 'start');
        $this->tagPlace('C', 'unicorn', 'end');
        $path = new WorkflowPlaceTaggedPath('unicorn', $this->workflow, $this->helper);
        $this->assertCount(3, $path->getPlaces());
        $this->assertCount(2, $path->getTransitions());
        $this->assertEquals(['A', 'B', 'C'], EntityUtils::mapToIds($path->getPlaces()));
        $this->assertEquals(['AB', 'BC'], EntityUtils::mapToIds($path->getTransitions()));
    }

    public function testBefore() {
        $this->tagPlace('A', 'unicorn', 'start');
        $this->tagPlace('C', 'unicorn', 'end');
        $path = new WorkflowPlaceTaggedPath('unicorn', $this->workflow, $this->helper);
        $this->assertTrue($path->isBefore('A', 'AB'));
        $this->assertTrue($path->isBefore('A', 'B'));
        $this->assertTrue($path->isBefore('A', 'C'));
        $this->assertTrue($path->isBefore('B', 'C'));
        $this->assertTrue($path->isBefore('AB', 'C'));
        $this->assertTrue($path->isBefore('B', 'BC'));
        $this->assertFalse($path->isBefore('BC', 'B'));
        $this->assertFalse($path->isBefore('C', 'B'));
        $this->assertFalse($path->isBefore('BC', 'A'));
    }

    public function testBeforeWithInvalidId() {
        $this->expectException(\InvalidArgumentException::class);
        $this->tagPlace('A', 'unicorn', 'start');
        $this->tagPlace('C', 'unicorn', 'end');
        $path = new WorkflowPlaceTaggedPath('unicorn', $this->workflow, $this->helper);
        $path->isBefore('X', 'AB');
    }

    public function testSelectingCorrectPath() {
        $this->tagPlace('A', 'unicorn', 'start');
        $this->tagPlace('C', 'unicorn', 'end');
        $this->tagPlace('D', 'rainbow', 'start');
        $this->tagPlace('E', 'rainbow', 'end');
        $path = new WorkflowPlaceTaggedPath('rainbow', $this->workflow, $this->helper);
        $this->assertEquals(['D', 'E'], EntityUtils::mapToIds($path->getPlaces()));
        $this->assertEquals(['DE'], EntityUtils::mapToIds($path->getTransitions()));
    }

    private function tagPlace(string $placeId, string $tagName, string $tagValue) {
        $places = array_map(
            function ($placeDef) use ($tagValue, $tagName, $placeId) {
                if ($placeDef instanceof ResourceWorkflowPlace) {
                    $placeDef = $placeDef->toArray();
                }
                if ($placeDef['id'] == $placeId) {
                    $placeDef['pluginsConfig'][] = [
                        'name' => ResourceWorkflowPlugin::getNameFromClassName(WorkflowPlaceTaggerResourceWorkflowPlugin::class),
                        'config' => [
                            'tagName' => $tagName,
                            'tagValue' => $tagValue,
                        ],
                    ];
                }
                return $placeDef;
            },
            $this->workflow->getPlaces()
        );
        $this->workflow->update($this->workflow->getName(), $places, $this->workflow->getTransitions());
    }
}
