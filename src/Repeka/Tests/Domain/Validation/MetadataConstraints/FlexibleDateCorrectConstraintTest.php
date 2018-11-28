<?php
namespace Repeka\Tests\Domain\Validation\MetadataConstraints;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\MetadataDateControl\FlexibleDate;
use Repeka\Domain\Exception\RespectValidationFailedException;
use Repeka\Domain\Validation\Exceptions\FlexibleDateControlMetadataCorrectStructureRuleException;
use Repeka\Domain\Validation\MetadataConstraints\FlexibleDateCorrectConstraint;
use Repeka\Tests\Traits\StubsTrait;

class FlexibleDateCorrectConstraintTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var FlexibleDateCorrectConstraint */
    private $rule;
    /** @var  Metadata */
    private $metadata1;
    /** @var  Metadata */
    private $dateMetadata;

    protected function setUp() {
        $this->metadata1 = $this->createMetadataMock(1);
        $this->dateMetadata = $this->createMetadataMock(3, null, MetadataControl::FLEXIBLE_DATE());
        $this->rule = (new FlexibleDateCorrectConstraint());
    }

    public function testAcceptsIfDateControlMetadataValidStructure() {
        $contents = [
            (new FlexibleDate('2018-09-13T16:39:49', '2018-09-13T16:39:49', 'day', null))->toArray(),
        ];
        $this->rule->validateAll($this->dateMetadata, $contents);
    }

    public function testAcceptMultipleValidDates() {
        $contents = [
            (new FlexibleDate('2018-09-13T16:39:49', '2018-09-13T16:39:49', 'day', null))->toArray(),
            (new FlexibleDate('2018-09-13T16:39:49', '2018-09-15T16:39:49', 'day', null))->toArray(),
        ];
        $this->rule->validateAll($this->dateMetadata, $contents);
    }

    public function testRejectsIfDateControlMetadataInvalidMode() {
        $this->expectException(\InvalidArgumentException::class);
        $contents = [
            (new FlexibleDate('2018-09-13T16:39:49', '2018-09-13T16:39:49', 'quarter', null))->toArray(),
        ];
        $this->rule->validateAll($this->dateMetadata, $contents);
    }

    public function testRejectsIfDateControlMetadataInvalidStructure() {
        $this->expectException(\InvalidArgumentException::class);
        $contents = [
            (new FlexibleDate(1238990, 3219799, 'day', null))->toArray(),
        ];
        $this->rule->validateAll($this->dateMetadata, $contents);
    }

    public function testRejectsWhenInvalidOrderOfDates() {
        $this->expectException(RespectValidationFailedException::class);
        $contents = [
            (new FlexibleDate('2018-09-13T16:39:49', '2018-09-12T16:39:49', 'range', 'day'))->toArray(),
        ];
        $this->rule->validateAll($this->dateMetadata, $contents);
    }

    public function testRejectsWhenInvalidRangeMode() {
        $this->expectException(FlexibleDateControlMetadataCorrectStructureRuleException::class);
        $contents = [
            (new FlexibleDate('2018-09-13T16:39:49', '2018-09-12T16:39:49', 'range', 'range'))->toArray(),
        ];
        $this->rule->validateAll($this->dateMetadata, $contents);
    }
}
