<?php
namespace Repeka\Tests\Domain\Validation\Rules;

use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\PageResult;
use Repeka\Domain\Validation\Rules\WorkflowPlacesForDeletionAreUnoccupiedRule;
use Repeka\Tests\Traits\StubsTrait;

/** @SuppressWarnings("PHPMD.LongVariable") */
class WorkflowPlacesForDeletionAreUnoccupiedTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var \PHPUnit_Framework_MockObject_MockObject|ResourceWorkflow */
    private $workflow;

    /** @var \PHPUnit_Framework_MockObject_MockObject|ResourceWorkflowPlace */
    private $placeA;
    private $placeB;

    private $placeAAsArray = [];
    private $placeBAsArray = [];

    private $idA;
    private $idB;

    private $resource;

    private $resourceRepository;

    private $rule;

    protected function setUp() {
        $this->idA = 'aaaaaaaaa';
        $this->idB = 'bbbbbbbbb';
        $this->placeA = $this->createWorkflowPlaceMock($this->idA);
        $this->placeB = $this->createWorkflowPlaceMock($this->idB);
        $this->placeAAsArray = [
            "requiredMetadataIds" => [],
            "pluginsConfig" => [],
            "assigneeMetadataIds" => [],
            "lockedMetadataIds" => [],
            "label" => [
                "EN" => "Imported",
                "PL" => "Zaimportowana",
            ],
            'id' => $this->idA,
        ];
        $this->placeBAsArray = [
            "requiredMetadataIds" => [],
            "pluginsConfig" => [],
            "assigneeMetadataIds" => [],
            "lockedMetadataIds" => [],
            "label" => [
                "EN" => "Imported",
                "PL" => "Zaimportowana",
            ],
            'id' => $this->idB,
        ];
        $this->workflow = $this->createMock(ResourceWorkflow::class, 1);
        $this->workflow->method('getPlaces')->willReturn([$this->placeA, $this->placeB]);
        $this->resource = $this->createResourceMock('1', null, [], [$this->idB => true]);
        $this->resourceRepository = $this->createRepositoryStub(ResourceRepository::class, [$this->resource]);
        $this->rule = new WorkflowPlacesForDeletionAreUnoccupiedRule($this->resourceRepository);
        $this->rule = $this->rule->forWorkflow($this->workflow);
    }

    public function testWithUnocuppiedWorkflowPlaceForDeletion() {
        $this->resourceRepository->method('findByQuery')->willReturn(new PageResult([], 0));
        $this->assertTrue($this->rule->validate([$this->placeBAsArray]));
    }

    public function testWithOcuppiedWorkflowPlaceForDeletion() {
        $this->resourceRepository->method('findByQuery')->willReturn(new PageResult([$this->resource], 1));
        $this->assertFalse($this->rule->validate([$this->placeAAsArray]));
    }

    public function testWithNoWorkflowPlacesForDeletion() {
        $this->assertTrue($this->rule->validate([$this->placeAAsArray, $this->placeBAsArray]));
    }
}
