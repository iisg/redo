<?php
namespace Repeka\Tests\Domain\Utils;

use Repeka\Domain\Utils\PrintableArray;

class PrintableArrayTest extends \PHPUnit_Framework_TestCase {
    public function testPrinting() {
        $this->assertEquals('1, 2, 3', (string)(new PrintableArray([1, 2, 3])));
    }

    public function testFlattening() {
        $array = new PrintableArray(
            [
                new PrintableArray([1]),
                new PrintableArray([2, 3]),
            ]
        );
        $this->assertEquals('1, 2, 3', (string)$array);
        $this->assertInstanceOf(PrintableArray::class, $array[0]);
        $this->assertCount(2, $array);
        $flat = $array->flatten();
        $this->assertEquals('1, 2, 3', (string)$flat);
        $this->assertEquals(1, $flat[0]);
        $this->assertCount(3, $flat);
    }
}
