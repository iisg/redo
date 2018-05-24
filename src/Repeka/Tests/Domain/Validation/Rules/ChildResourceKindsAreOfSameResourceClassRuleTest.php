<?php
namespace Repeka\Tests\Domain\Validation\Rules;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\Validation\Rules\ChildResourceKindsAreOfSameResourceClassRule;

class ChildResourceKindsAreOfSameResourceClassRuleTest extends \PHPUnit_Framework_TestCase {
    /** @var  ResourceKindRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $resourceKindRepository;
    /** @var  ResourceKind|\PHPUnit_Framework_MockObject_MockObject */
    private $bookResourceKind;
    /** @var  ResourceKind|\PHPUnit_Framework_MockObject_MockObject */
    private $dictionaryResourceKind;
    /** @var  Metadata|\PHPUnit_Framework_MockObject_MockObject */
    private $parentMetadata;
    /** @var  Metadata|\PHPUnit_Framework_MockObject_MockObject */
    private $titleMetadata;
    /** @var ChildResourceKindsAreOfSameResourceClassRule */
    private $rule;

    private $metadataList;

    protected function setUp() {
        $this->resourceKindRepository = $this->createMock(ResourceKindRepository::class);
        $this->bookResourceKind = $this->createMock(ResourceKind::class);
        $this->bookResourceKind->expects($this->any())->method('getResourceClass')->willReturn('books');
        $this->dictionaryResourceKind = $this->createMock(ResourceKind::class);
        $this->dictionaryResourceKind->expects($this->any())->method('getResourceClass')->willReturn('dictionaries');
        $this->parentMetadata = $this->createMock(Metadata::class);
        $this->parentMetadata->expects($this->any())->method('getId')->willReturn(SystemMetadata::PARENT);
        $this->titleMetadata = $this->createMock(Metadata::class);
        $this->titleMetadata->expects($this->any())->method('getId')->willReturn(1);
        $this->titleMetadata->expects($this->any())->method('getResourceClass')->willReturn('books');
        $this->rule = new ChildResourceKindsAreOfSameResourceClassRule($this->resourceKindRepository);
    }

    public function testNegativeWhenNoParentMetadata() {
        $this->metadataList = [$this->titleMetadata];
        $this->assertFalse($this->rule->validate($this->metadataList));
    }

    public function testNegativeWhenNoOtherMetadata() {
        $this->metadataList = [$this->parentMetadata];
        $this->assertFalse($this->rule->validate($this->metadataList));
    }

    public function testNegativeWhenParentMetadataHasChildRKOfDifferentResourceClass() {
        $this->metadataList = [$this->parentMetadata, $this->titleMetadata];
        $this->parentMetadata->expects($this->any())->method('getConstraints')->willReturn(['resourceKind' => [1]]);
        $this->resourceKindRepository->expects($this->atLeastOnce())->method('findOne')->willReturn($this->dictionaryResourceKind);
        $this->assertFalse($this->rule->validate($this->metadataList));
    }

    public function testPositiveWhenParentMetadataHasChildRKOfSameResourceClass() {
        $this->metadataList = [$this->parentMetadata, $this->titleMetadata];
        $this->parentMetadata->expects($this->any())->method('getConstraints')->willReturn(['resourceKind' => [1]]);
        $this->resourceKindRepository->expects($this->atLeastOnce())->method('findOne')->willReturn($this->bookResourceKind);
        $this->assertTrue($this->rule->validate($this->metadataList));
    }

    public function testPositiveWhenParentMetadataHasNoChildResourceKinds() {
        $this->metadataList = [$this->parentMetadata, $this->titleMetadata];
        $this->parentMetadata->expects($this->any())->method('getConstraints')->willReturn(['resourceKind' => []]);
        $this->assertTrue($this->rule->validate($this->metadataList));
    }
}
