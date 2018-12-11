<?php
namespace Repeka\Tests\Domain\Validation\Strippers;

use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
use Repeka\Domain\Validation\Strippers\NonExistingMetadataStripper;
use Repeka\Tests\Traits\StubsTrait;

class NonExistingMetadataStripperTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;
    /** @var  NonExistingMetadataStripper */
    private $nonExistingMetadataStripper;

    protected function setUp() {
        $metadata = $this->createMetadataMock();
        $metadataRepository = $this->createMetadataRepositoryStub([$metadata]);
        $metadataRepository->method('findByQuery')->willReturn([$metadata]);
        $this->nonExistingMetadataStripper = new NonExistingMetadataStripper($metadataRepository);
    }

    public function testFiltersNonExistingMetadata() {
        $places = [
            new ResourceWorkflowPlace(['PL' => 'label1'], 'id1', [1, 2]),
            new ResourceWorkflowPlace(['PL' => 'label2'], 'id2', [1], [2]),
        ];
        $result = $this->nonExistingMetadataStripper->removeNonExistingMetadata($places, 'books');
        $expectedPlaces = [
            new ResourceWorkflowPlace(['PL' => 'label1'], 'id1', [1]),
            new ResourceWorkflowPlace(['PL' => 'label2'], 'id2', [1]),
        ];
        $this->assertEquals($expectedPlaces, $result);
    }

    public function testDoesNotPreserveKeysOfFilteredOutMetadata() {
        $places = [new ResourceWorkflowPlace(['PL' => 'label1'], 'id1', [2, 1])];
        $result = $this->nonExistingMetadataStripper->removeNonExistingMetadata($places, 'books');
        $expectedPlaces = [new ResourceWorkflowPlace(['PL' => 'label1'], 'id1', [1])];
        $this->assertEquals($expectedPlaces, $result);
    }
}
