<?php
namespace Repeka\Tests\Domain\Entity;

use Repeka\Domain\Entity\MetadataValue;
use Repeka\Tests\Traits\StubsTrait;

class MetadataValueTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    public function testCreation() {
        $value = new MetadataValue(['value' => 'A']);
        $this->assertEquals('A', $value->getValue());
        $this->assertEmpty($value->getSubmetadata());
    }

    public function testFromValue() {
        $value = new MetadataValue('unicorn');
        $this->assertEquals('unicorn', $value->getValue());
        $this->assertEmpty($value->getSubmetadata());
    }

    public function testCreationWithSubmetadata() {
        $value = new MetadataValue(['value' => 'A', 'submetadata' => [2 => [['value' => 'B']]]]);
        $this->assertEquals('A', $value->getValue());
        $this->assertCount(1, $value->getSubmetadata());
        $this->assertCount(1, $value->getSubmetadata()[2]);
        $this->assertCount(1, $value->getSubmetadata(2));
        $this->assertEquals('B', $value->getSubmetadata()[2][0]->getValue());
        $this->assertEquals('B', $value->getSubmetadata(2)[0]->getValue());
    }

    public function testCreationWithSubmetadataAndNullValue() {
        $value = new MetadataValue(['value' => null, 'submetadata' => [2 => [['value' => 'B']]]]);
        $this->assertEquals(null, $value->getValue());
        $this->assertCount(1, $value->getSubmetadata());
    }

    public function testGettingUnknownSubmetadata() {
        $value = new MetadataValue(['value' => 'A']);
        $this->assertEmpty($value->getSubmetadata(44));
    }

    public function testValueToArray() {
        $valueArray = ['value' => 'A', 'submetadata' => [2 => [['value' => 'B']]]];
        $value = new MetadataValue($valueArray);
        $this->assertEquals($valueArray, $value->toArray());
    }

    public function testValueToArrayNoSubmetadata() {
        $valueArray = ['value' => 'A'];
        $value = new MetadataValue($valueArray);
        $this->assertEquals($valueArray, $value->toArray());
    }
}
