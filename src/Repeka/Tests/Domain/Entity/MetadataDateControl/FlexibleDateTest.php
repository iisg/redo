<?php
namespace Repeka\Tests\Domain\Entity\MetadataDateControl;

use Repeka\Domain\Entity\MetadataDateControl\FlexibleDate;
use Repeka\Domain\Entity\MetadataDateControl\MetadataDateControlMode;
use Repeka\Domain\Validation\Exceptions\FlexibleDateControlMetadataCorrectStructureRuleException;

class FlexibleDateTest extends \PHPUnit_Framework_TestCase {
    private $dateFromAtom;
    private $dateToAtom;

    protected function setUp() {
        $this->dateFromAtom = '2018-09-13T16:39:49+02:00';
        $this->dateToAtom = '2018-09-13T16:39:49+02:00';
    }

    public function testCreatingFlexibleDateFromArray() {
        $data = ['from' => $this->dateFromAtom, 'to' => $this->dateToAtom, 'mode' => MetadataDateControlMode::DAY];
        $flexibleDate = FlexibleDate::fromArray($data);
        $this->assertEquals($this->dateFromAtom, $flexibleDate->getFrom());
        $this->assertEquals($this->dateToAtom, $flexibleDate->getTo());
        $this->assertEquals(MetadataDateControlMode::DAY, $flexibleDate->getMode());
        $this->assertEquals(null, $flexibleDate->getRangeMode());
        $this->assertEquals('13.09.2018', $flexibleDate->getDisplayValue());
    }

    public function testConvertingFlexibleDateToArray() {
        $flexibleDate = new FlexibleDate($this->dateFromAtom, $this->dateToAtom, MetadataDateControlMode::MONTH, null);
        $data = [
            'from' => $this->dateFromAtom,
            'to' => $this->dateToAtom,
            'mode' => MetadataDateControlMode::MONTH,
            'rangeMode' => null,
            'displayValue' => '09.2018',
        ];
        $this->assertEquals($data, $flexibleDate->toArray());
    }

    public function testCreatingFlexibleDateWithRangeMode() {
        $flexibleDate = new FlexibleDate(
            $this->dateFromAtom,
            '2018-10-13T16:39:49+02:00',
            MetadataDateControlMode::RANGE,
            MetadataDateControlMode::MONTH
        );
        $this->assertEquals($this->dateFromAtom, $flexibleDate->getFrom());
        $this->assertEquals('2018-10-13T16:39:49+02:00', $flexibleDate->getTo());
        $this->assertEquals(MetadataDateControlMode::RANGE, $flexibleDate->getMode());
        $this->assertEquals(MetadataDateControlMode::MONTH, $flexibleDate->getRangeMode());
        $this->assertEquals('09.2018 - 10.2018', $flexibleDate->getDisplayValue());
    }

    public function testThrowingExceptionWhenBadRangeModeGiven() {
        $this->expectException(FlexibleDateControlMetadataCorrectStructureRuleException::class);
        new FlexibleDate($this->dateFromAtom, $this->dateToAtom, MetadataDateControlMode::RANGE, 'badMode');
    }
}
