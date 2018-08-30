<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\UseCase\Resource\ResourceGodUpdateCommand;
use Repeka\Domain\UseCase\Resource\ResourceGodUpdateCommandAdjuster;
use Repeka\Domain\Utils\EntityUtils;
use Repeka\Tests\Traits\StubsTrait;

class ResourceGodUpdateCommandAdjusterTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var  ResourceGodUpdateCommandAdjuster */
    private $adjuster;
    private $resourceKind;
    private $resourceKind2;

    protected function setUp() {
        $realMetadata = Metadata::create('books', MetadataControl::RELATIONSHIP(), 'name', ['PL' => 'A']);
        EntityUtils::forceSetId($realMetadata, 11);
        $this->resourceKind = $this->createResourceKindMock(1, 'book', [$realMetadata]);
        $this->resourceKind2 = $this->createResourceKindMock(2, 'book', [$realMetadata]);
        $resourceKindRepository = $this->createRepositoryStub(ResourceKindRepository::class, [$this->resourceKind2]);
        $metadataRepository = $this->createRepositoryStub(MetadataRepository::class);
        $this->adjuster = new ResourceGodUpdateCommandAdjuster($resourceKindRepository, $metadataRepository);
    }

    public function testConvertResourceKindIdsToResourceKinds() {
        $resource = $this->createResourceMock(1, $this->resourceKind);
        $command = ResourceGodUpdateCommand::builder()->setResource($resource)->changeResourceKind($this->resourceKind2->getId())->build();
        $command = $this->adjuster->adjustCommand($command);
        $this->assertEquals($this->resourceKind2, $command->getResourceKind());
    }
}
