<?php
namespace Repeka\Domain\Metadata\MetadataValueAdjuster;

use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\MetadataValue;
use Repeka\Tests\Traits\StubsTrait;
use Respect\Validation\Exceptions\ValidationException;

class DoubleMetadataValueAdjusterTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var DoubleMetadataValueAdjuster */
    private $adjuster;

    protected function setUp() {
        $this->adjuster = new DoubleMetadataValueAdjuster();
    }

    /** @dataProvider adjusterTests */
    public function testAdjuster($input, $expectedOutput) {
        $value = new MetadataValue($input);
        $metadata = $this->createMetadataMock(1, null, MetadataControl::DOUBLE());
        try {
            $this->assertEquals($expectedOutput, $this->adjuster->adjustMetadataValue($value, $metadata)->getValue());
        } catch (ValidationException $e) {
            $this->assertNull($expectedOutput, 'No failure was expected.');
        }
    }

    public function adjusterTests() {
        return [
            ['2', 2],
            ['2,2', 2.2],
            ['2.2', 2.2],
            ['2.2.2', 2.2],
            ['2.54 MB', 2.54],
            ['   2 8', 2],
            ['ala', null],
            ['', null],
            ['0', 0],
            ['0.0', 0],
            ['0.1', .1],
            ['-5.4', -5.4],
            [0, 0],
            [null, 0]
        ];
    }
}
