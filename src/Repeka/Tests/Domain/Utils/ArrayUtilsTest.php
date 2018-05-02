<?php
namespace Repeka\Tests\Domain\Utils;

use PHPUnit\Framework\AssertionFailedError;
use Repeka\Domain\Utils\ArrayUtils;

class ArrayUtilsTest extends \PHPUnit_Framework_TestCase {

    public function testGroupBy() {
        $values = [-1, 2, 1];
        $result = ArrayUtils::groupBy($values, 'abs');
        $this->assertEquals([1 => [0 => -1, 2 => 1], 2 => [1 => 2]], $result);
    }

    public function testGroupByEmptyArray() {
        $values = [];
        $mapper = function () {
            throw new AssertionFailedError('Mapper should not be called');
        };
        $result = ArrayUtils::groupBy($values, $mapper);
        $this->assertEquals([], $result);
    }

    public function testGroupByEmptyStringGroupName() {
        $values = ["", "abc"];
        $mapper = function ($value) {
            return $value;
        };
        $result = ArrayUtils::groupBy($values, $mapper);
        $this->assertEquals(["" => [0 => ""], "abc" => [1 => "abc"]], $result);
    }

    public function testGroupByPreservingKeys() {
        $values = ['abc' => -1, 'def' => 2, 'ghi' => 1];
        $mapper = function ($n) {
            return abs($n);
        };
        $result = ArrayUtils::groupBy($values, $mapper);
        $this->assertEquals([1 => ['abc' => -1, 'ghi' => 1], 2 => ['def' => 2]], $result);
    }

    public function testRemoveValuesShorterThan() {
        $values = [[1, 2, 3], [1, 2], [1, 2, 3, 4]];
        $minLength = 3;
        $result = ArrayUtils::removeValuesShorterThan($values, $minLength);
        $this->assertEquals([0 => [1, 2, 3], 2 => [1, 2, 3, 4]], $result);
    }

    public function testRemoveValuesShorterThanPreservingKeys() {
        $values = ['three' => [1, 2, 3], 'two' => [1, 2], 'four' => [1, 2, 3, 4]];
        $minLength = 3;
        $result = ArrayUtils::removeValuesShorterThan($values, $minLength);
        $this->assertEquals(['three' => [1, 2, 3], 'four' => [1, 2, 3, 4]], $result);
    }

    public function testRemoveValuesShorterThanResultCanBeEmpty() {
        $values = [[1, 2, 3], [1, 2], [1, 2, 3, 4]];
        $minLength = 5;
        $result = ArrayUtils::removeValuesShorterThan($values, $minLength);
        $this->assertEquals([], $result);
    }

    public function testAllEqual() {
        $values = ['abc', 'def', 'ghi'];
        $mapper = function ($value) {
            return strlen($value);
        };
        $result = ArrayUtils::allEqual($values, $mapper);
        $this->assertTrue($result);
    }

    public function testAllEqualEmptyArray() {
        $values = [];
        $mapper = function () {
            throw new AssertionFailedError('Mapper should not be called');
        };
        $result = ArrayUtils::allEqual($values, $mapper);
        $this->assertTrue($result);
    }

    public function testAllEqualDifferentValues() {
        $values = ['abc', 'def', 'ghij'];
        $mapper = function ($value) {
            return strlen($value);
        };
        $result = ArrayUtils::allEqual($values, $mapper);
        $this->assertFalse($result);
    }
}
