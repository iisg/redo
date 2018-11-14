<?php
namespace Repeka\Tests\Application\Upload;

use Repeka\Application\Twig\Paginator;

class PaginatorTest extends \PHPUnit_Framework_TestCase {

    public function testSinglePage() {
        // [1]
        $paginator = new Paginator(2, 2, 3, 3);
        $pages = $paginator->paginate(1, 1);
        $this->assertEquals(1, $pages['current']);
        $this->assertEmpty($pages['first']);
        $this->assertEmpty($pages['left']);
        $this->assertEmpty($pages['right']);
        $this->assertEmpty($pages['last']);
        $this->assertFalse($pages['leftEllipsis']);
        $this->assertFalse($pages['rightEllipsis']);
    }

    public function testPagesWithEllipses() {
        // 1 2 ... 7 8 9 [10] 11 12 13 ... 19 20
        $paginator = new Paginator(2, 2, 3, 3);
        $pages = $paginator->paginate(10, 20);
        $this->assertEquals(10, $pages['current']);
        $this->assertEquals([1, 2], $pages['first']);
        $this->assertEquals([7, 8, 9], $pages['left']);
        $this->assertEquals([11, 12, 13], $pages['right']);
        $this->assertEquals([19, 20], $pages['last']);
        $this->assertTrue($pages['leftEllipsis']);
        $this->assertTrue($pages['rightEllipsis']);
    }

    public function testFirstPage() {
        // [1] 2 3 4  ... 9 10
        $paginator = new Paginator(2, 2, 3, 3);
        $pages = $paginator->paginate(1, 10);
        $this->assertEquals(1, $pages['current']);
        $this->assertEmpty($pages['first']);
        $this->assertEmpty($pages['left']);
        $this->assertEquals([2, 3, 4], $pages['right']);
        $this->assertEquals([9, 10], $pages['last']);
        $this->assertFalse($pages['leftEllipsis']);
        $this->assertTrue($pages['rightEllipsis']);
    }

    public function testLastPage() {
        // 1 2 ... 7 8 9 [10]
        $paginator = new Paginator(2, 2, 3, 3);
        $pages = $paginator->paginate(10, 10);
        $this->assertEquals(10, $pages['current']);
        $this->assertEquals([1, 2], $pages['first']);
        $this->assertEquals([7, 8, 9], $pages['left']);
        $this->assertEmpty($pages['right']);
        $this->assertEmpty($pages['last']);
        $this->assertTrue($pages['leftEllipsis']);
        $this->assertFalse($pages['rightEllipsis']);
        $this->assertTrue($pages['showCurrent']);
    }

    public function testMiddlePagesContiguousToEndings() {
        // 1 2 3 4 5 [6] 7 8 9 10 11
        $paginator = new Paginator(2, 2, 3, 3);
        $pages = $paginator->paginate(6, 11);
        $this->assertEquals(6, $pages['current']);
        $this->assertEquals([1, 2], $pages['first']);
        $this->assertEquals([3, 4, 5], $pages['left']);
        $this->assertEquals([7, 8, 9], $pages['right']);
        $this->assertEquals([10, 11], $pages['last']);
        $this->assertFalse($pages['leftEllipsis']);
        $this->assertFalse($pages['rightEllipsis']);
    }

    public function testMiddlePagesOverlapWithEnding() {
        // 1 2 3 4 [5] 6 7 8 9
        $paginator = new Paginator(2, 2, 3, 3);
        $pages = $paginator->paginate(5, 9);
        $this->assertEquals(5, $pages['current']);
        $this->assertEquals([1, 2, 3, 4], array_merge($pages['first'], $pages['left']));
        $this->assertEquals([6, 7, 8, 9], array_merge($pages['right'], $pages['last']));
        $this->assertFalse($pages['leftEllipsis']);
        $this->assertFalse($pages['rightEllipsis']);
    }

    public function testCurrentOutsideRange() {
        // 1 2 ... 8 9 10
        $paginator = new Paginator(2, 2, 3, 3);
        $pages = $paginator->paginate(11, 10);
        $this->assertFalse($pages['showCurrent']);
        $this->assertEquals([1, 2], $pages['first']);
        $this->assertEquals([8, 9, 10], $pages['left']);
        $this->assertEmpty($pages['right']);
        $this->assertEmpty($pages['last']);
        $this->assertTrue($pages['leftEllipsis']);
        $this->assertFalse($pages['rightEllipsis']);
    }
}
