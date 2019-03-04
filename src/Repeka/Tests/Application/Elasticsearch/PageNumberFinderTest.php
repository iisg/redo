<?php
namespace Repeka\Tests\Application\Elasticsearch;

use Repeka\Application\Elasticsearch\PageNumberFinder;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Service\ResourceFileStorage;
use Repeka\Tests\Traits\StubsTrait;

class PageNumberFinderTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceFileStorage */
    private $fileStorage;
    /** @var PageNumberFinder */
    private $pageNumberFinder;

    private $files = [
        'simple-test.txt' =>
            "This is some text on the very first page\nAnd another one\n\fAnd here we have some words on second page\n\f",
        'simple-test-bis.txt' =>
            "Page breaks do not break\n\fSome ending line\n\f",
        'page-break.txt' =>
            "This is some text on the very first page\n\fAnd here we have some words on second page\n\f",
        'no-page-breaks.txt' =>
            "The best thing about a boolean is even if you are wrong,\n you are only off by a bit\n",
        'too-many-page-breaks.txt' =>
            "Page breaks tend to break things\n\f\n\fSo let's write more of those\n\f",
    ];

    public function setUp() {
        $this->fileStorage = $this->createMock(ResourceFileStorage::class);
        $this->pageNumberFinder = new PageNumberFinder($this->fileStorage);
    }

    public function testAllPageNumbersFound() {
        $this->fileStorage->method('getFileContents')->willReturn($this->files['simple-test.txt']);
        $highlights = ["some text on the very first <em>page</em>", "some words on second <em>page</em>\n"];
        $expectedResult = [
            [PageNumberFinder::PAGE_NUMBER => 1, PageNumberFinder::HIGHLIGHT => "some text on the very first <em>page</em>"],
            [PageNumberFinder::PAGE_NUMBER => 2, PageNumberFinder::HIGHLIGHT => "some words on second <em>page</em>\n"],
        ];
        $result = $this->pageNumberFinder->matchSearchHitsWithPageNumbers(
            $this->createMock(ResourceEntity::class),
            MetadataControl::FILE,
            ['simple-test.txt'],
            $highlights
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function testPageNumbersFoundInMultipleFiles() {
        $path1 = 'simple-test.txt';
        $path2 = 'simple-test-bis.txt';
        $resource = $this->createMock(ResourceEntity::class);
        $this->fileStorage->method('getFileContents')->will(
            $this->returnValueMap(
                [[$resource, $path1, $this->files[$path1]], [$resource, $path2, $this->files[$path2]]]
            )
        );
        $highlights = [
            "some text on the very first <em>page</em>",
            "some words on second <em>page</em>\n",
            "<em>Page</em> breaks do not break\n",
        ];
        $expectedResult = [
            [PageNumberFinder::PAGE_NUMBER => 1, PageNumberFinder::HIGHLIGHT => "some text on the very first <em>page</em>"],
            [PageNumberFinder::PAGE_NUMBER => 2, PageNumberFinder::HIGHLIGHT => "some words on second <em>page</em>\n"],
            [PageNumberFinder::PAGE_NUMBER => 1, PageNumberFinder::HIGHLIGHT => "<em>Page</em> breaks do not break\n"],
        ];
        $result = $this->pageNumberFinder->matchSearchHitsWithPageNumbers(
            $resource,
            MetadataControl::FILE,
            [$path1, $path2],
            $highlights
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function testHitsFoundBeforePageBreak() {
        $this->fileStorage->method('getFileContents')->willReturn($this->files['page-break.txt']);
        $highlights = ["very <em>first</em> page\n\fAnd here we have"];
        $expectedResult = [[PageNumberFinder::PAGE_NUMBER => 1, PageNumberFinder::HIGHLIGHT => "very <em>first</em> page\n"]];
        $result = $this->pageNumberFinder->matchSearchHitsWithPageNumbers(
            $this->createMock(ResourceEntity::class),
            MetadataControl::FILE,
            ['page-break.txt'],
            $highlights
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function testHitsFoundAfterPageBreak() {
        $this->fileStorage->method('getFileContents')->willReturn($this->files['page-break.txt']);
        $highlights = ["very first page\n\f<em>And</em> here we have"];
        $expectedResult = [[PageNumberFinder::PAGE_NUMBER => 2, PageNumberFinder::HIGHLIGHT => "<em>And</em> here we have"]];
        $result = $this->pageNumberFinder->matchSearchHitsWithPageNumbers(
            $this->createMock(ResourceEntity::class),
            MetadataControl::FILE,
            ['page-break.txt'],
            $highlights
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function testFilesWithNoPageBreaksTreatedAsOnePage() {
        $this->fileStorage->method('getFileContents')->willReturn($this->files['no-page-breaks.txt']);
        $highlights = ["is even if <em>you</em> are wrong", "<em>you</em> are only off by a bit\n"];
        $expectedResult = [
            [PageNumberFinder::PAGE_NUMBER => 1, PageNumberFinder::HIGHLIGHT => "is even if <em>you</em> are wrong"],
            [PageNumberFinder::PAGE_NUMBER => 1, PageNumberFinder::HIGHLIGHT => "<em>you</em> are only off by a bit\n"],
        ];
        $result = $this->pageNumberFinder->matchSearchHitsWithPageNumbers(
            $this->createMock(ResourceEntity::class),
            MetadataControl::FILE,
            ['no-page-breaks.txt'],
            $highlights
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function testHitWithMoreThanOnePageBreak() {
        $this->fileStorage->method('getFileContents')->willReturn($this->files['too-many-page-breaks.txt']);
        $highlights = ["to break things\n\f\n\fSo let's <em>write</em> more"];
        $expectedResult = [
            [PageNumberFinder::PAGE_NUMBER => 3, PageNumberFinder::HIGHLIGHT => "So let's <em>write</em> more"],
        ];
        $result = $this->pageNumberFinder->matchSearchHitsWithPageNumbers(
            $this->createMock(ResourceEntity::class),
            MetadataControl::FILE,
            ['too-many-page-breaks.txt'],
            $highlights
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function testFindingPageNumbersInDirectoryContents() {
        $this->fileStorage->method('getDirectoryContents')->willReturn(['subdir', 'file.php', 'file1.txt', 'file2.txt']);
        $this->fileStorage->method('getFileContents')->willReturnOnConsecutiveCalls(
            "Some content on the first page\n\fAnd on second page\n\fAnd on the third\n\f",
            "Some content on the first page from second file\n\fsome page\n\fother stuff\n\f"
        );
        $highlights = [
            "<em>Some</em> content on the first page\n\fAnd",
            "<em>Some</em> content on the first page from second file\n\f<em>some</em> page",
        ];
        $adjustedHighlights = $this->pageNumberFinder->matchSearchHitsWithPageNumbers(
            $this->createResourceMock(1),
            MetadataControl::DIRECTORY,
            [__DIR__],
            $highlights
        );
        $expectedResult = [
            [
                PageNumberFinder::PAGE_NUMBER => 1,
                PageNumberFinder::HIGHLIGHT => "<em>Some</em> content on the first page\n",
            ],
            [
                PageNumberFinder::PAGE_NUMBER => 1,
                PageNumberFinder::HIGHLIGHT => "<em>Some</em> content on the first page from second file\n",
            ],
        ];
        $this->assertEquals($expectedResult, $adjustedHighlights);
    }
}
