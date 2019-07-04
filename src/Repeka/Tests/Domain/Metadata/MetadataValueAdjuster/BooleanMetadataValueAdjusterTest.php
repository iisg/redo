<?php
namespace Repeka\Domain\Metadata\MetadataValueAdjuster;

use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\MetadataValue;
use Repeka\Tests\Traits\StubsTrait;

class BooleanMetadataValueAdjusterTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var BooleanMetadataValueAdjuster */
    private $adjuster;

    protected function setUp() {
        $this->adjuster = new BooleanMetadataValueAdjuster();
    }

    /** @dataProvider booleanExamples */
    public function testAdjustingBooleans($input, bool $expected) {
        $metadata = $this->createMetadataMock(1, null, MetadataControl::BOOLEAN());
        $value = new MetadataValue($input);
        $this->assertEquals($expected, $this->adjuster->adjustMetadataValue($value, $metadata)->getValue());
    }

    public function booleanExamples() {
        return [
            ['', false],
            ['0', false],
            ['1', true],
            ['true', true],
            ['false', false],
            ['unicorn', true],
        ];
    }
}
