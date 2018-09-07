<?php
namespace Repeka\Tests\Domain\Validation\Rules;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\MetadataDateControl\FlexibleDate;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Validation\Exceptions\FlexibleDateControlMetadataCorrectStructureRuleException;
use Repeka\Domain\Validation\Rules\FlexibleDateControlMetadataCorrectStructureRule;
use Repeka\Tests\Traits\StubsTrait;

class FlexibleDateControlMetadataCorrectStructureRuleTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var FlexibleDateControlMetadataCorrectStructureRule */
    private $rule;
    /** @var  Metadata */
    private $metadata1;
    /** @var  Metadata */
    private $dateMetadata;

    protected function setUp() {
        $this->metadata1 = $this->createMetadataMock(1);
        $this->dateMetadata = $this->createMetadataMock(3, null, MetadataControl::FLEXIBLE_DATE());
        $metadataRepository = $this->createRepositoryStub(MetadataRepository::class, [$this->metadata1, $this->dateMetadata]);
        $resourceKind = $this->createResourceKindMock(1, 'books', [$this->metadata1, $this->dateMetadata]);
        $this->rule = (new FlexibleDateControlMetadataCorrectStructureRule($metadataRepository))->forResourceKind($resourceKind);
    }

    public function testAcceptsIfNoDateControlMetadata() {
        $contents = ResourceContents::fromArray([
            $this->metadata1->getId() => '',
        ]);
        $this->assertTrue($this->rule->validate($contents));
    }

    public function testAcceptsIfDateControlMetadataValidStructure() {
        $contents = ResourceContents::fromArray([
            $this->dateMetadata->getId() => [[
                'value' => (new FlexibleDate('2018-09-13T16:39:49', '2018-09-13T16:39:49', 'day', null))->toArray(),
            ]],
            $this->metadata1->getId() => '',
        ]);
        $this->assertTrue($this->rule->validate($contents));
    }

    public function testRejectsIfDateControlMetadataInvalidMode() {
        $this->expectException(\InvalidArgumentException::class);
        $contents = ResourceContents::fromArray([
            $this->dateMetadata->getId() => [[
                'value' => (new FlexibleDate('2018-09-13T16:39:49', '2018-09-13T16:39:49', 'quarter', null))->toArray(),
            ]],
            $this->metadata1->getId() => '',
        ]);
        $this->rule->validate($contents);
    }

    public function testRejectsIfDateControlMetadataInvalidStructure() {
        $this->expectException(\InvalidArgumentException::class);
        $contents = ResourceContents::fromArray([
            $this->dateMetadata->getId() => [[
                'value' => (new FlexibleDate(1238990, 3219799, 'day', null))->toArray(),
            ]],
            $this->metadata1->getId() => '',
        ]);
        $this->rule->validate($contents);
    }

    public function testRejectsWhenInvalidOrderOfDates() {
        $this->expectException(\InvalidArgumentException::class);
        $contents = ResourceContents::fromArray([
            $this->dateMetadata->getId() => [[
                'value' => (new FlexibleDate('2018-09-13T16:39:49', '2018-09-12T16:39:49', 'range', 'day'))->toArray(),
            ]],
            $this->metadata1->getId() => '',
        ]);
        $this->rule->validate($contents);
    }

    public function testRejectsWhenInvalidRangeMode() {
        $this->expectException(FlexibleDateControlMetadataCorrectStructureRuleException::class);
        $contents = ResourceContents::fromArray([
            $this->dateMetadata->getId() => [[
                'value' => (new FlexibleDate('2018-09-13T16:39:49', '2018-09-12T16:39:49', 'range', 'range'))->toArray(),
            ]],
            $this->metadata1->getId() => '',
        ]);
        $this->rule->validate($contents);
    }
}
