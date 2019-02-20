<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Metadata\MetadataValueAdjuster\ResourceContentsAdjuster;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommandAdjuster;
use Repeka\Domain\Utils\EntityUtils;
use Repeka\Tests\Traits\StubsTrait;

class ResourceTransitionCommandAdjusterTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var  ResourceTransitionCommandAdjuster */
    private $adjuster;
    private $resourceKind;
    private $transition;

    protected function setUp() {
        $realMetadata = Metadata::create('books', MetadataControl::RELATIONSHIP(), 'name', ['PL' => 'A']);
        EntityUtils::forceSetId($realMetadata, 11);
        $workflow = $this->createMock(ResourceWorkflow::class);
        $this->transition = $this->createWorkflowTransitionMock([], [], [], 't1');
        $workflow->method('getTransition')->willReturn($this->transition);
        $this->resourceKind = $this->createResourceKindMock(1, 'book', [$realMetadata], $workflow);
        $this->adjuster = new ResourceTransitionCommandAdjuster($this->createMock(ResourceContentsAdjuster::class));
    }

    public function testConvertTransitionIdToTransition() {
        $command = new ResourceTransitionCommand(
            new ResourceEntity($this->resourceKind, ResourceContents::empty()),
            ResourceContents::fromArray([11 => 2]),
            't1'
        );
        $command = $this->adjuster->adjustCommand($command);
        $this->assertEquals($this->transition, $command->getTransition());
    }
}
