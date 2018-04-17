<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Constants\SystemTransition;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommandAdjuster;
use Repeka\Domain\Utils\EntityUtils;
use Repeka\Tests\Traits\StubsTrait;

class ResourceTransitionCommandAdjusterTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var  ResourceTransitionCommandAdjuster */
    private $adjuster;
    private $resourceKind;
    private $resource;
    private $transition;

    protected function setUp() {
        $realMetadata = Metadata::create('books', MetadataControl::RELATIONSHIP(), 'name', ['PL' => 'A']);
        EntityUtils::forceSetId($realMetadata, 11);
        $this->resource = $this->createResourceMock(2);
        $workflow = $this->createMock(ResourceWorkflow::class);
        $this->transition = $this->createWorkflowTransitionMock([], [], [], 't1');
        $workflow->method('getTransition')->willReturn($this->transition);
        $this->resourceKind = $this->createResourceKindMock(1, 'book', [$realMetadata], $workflow);
        $metadataRepository = $this->createRepositoryStub(
            MetadataRepository::class,
            [
                SystemMetadata::PARENT()->toMetadata(),
                $this->createMetadataMock(55),
                $realMetadata,
            ]
        );
        $resourceRepository = $this->createRepositoryStub(ResourceRepository::class, [$this->resource]);
        $this->adjuster = new ResourceTransitionCommandAdjuster($metadataRepository, $resourceRepository);
    }

    public function testConvertResourceIdToResourceEntity() {
        $command = new ResourceTransitionCommand(
            new ResourceEntity($this->resourceKind, ResourceContents::empty()),
            ResourceContents::fromArray([11 => 2]),
            SystemTransition::CREATE()->toTransition($this->resourceKind)
        );
        $command = $this->adjuster->adjustCommand($command);
        $this->assertEquals($this->resource, $command->getContents()->getValues(11)[0]);
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
