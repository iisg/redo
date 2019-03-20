<?php
namespace Repeka\Tests\Application\Upload;

use Repeka\Application\Elasticsearch\PageNumberFinder;
use Repeka\Application\Twig\FrontendConfig;
use Repeka\Application\Twig\Paginator;
use Repeka\Application\Twig\TwigFrontendExtension;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\Service\FileSystemDriver;
use Repeka\Domain\Service\ResourceFileStorage;
use Repeka\Tests\Traits\StubsTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class TwigFrontendExtensionTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceKindRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $resourceKindRepository;
    /** @var MetadataRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $metadataRepository;
    /** @var Paginator|\PHPUnit_Framework_MockObject_MockObject */
    private $paginator;
    /** @var PageNumberFinder|\PHPUnit_Framework_MockObject_MockObject */
    private $pageNumberFinder;
    /** @var FrontendConfig|\PHPUnit_Framework_MockObject_MockObject */
    private $frontendConfig;
    /** @var ResourceFileStorage|\PHPUnit_Framework_MockObject_MockObject */
    private $resourceFileStorage;
    /** @var FileSystemDriver|\PHPUnit_Framework_MockObject_MockObject */
    private $fileSystemDriver;
    /** @var TwigFrontendExtension */
    private $extension;

    /** @before */
    public function init() {
        $this->resourceKindRepository = $this->createMock(ResourceKindRepository::class);
        $this->metadataRepository = $this->createMock(MetadataRepository::class);
        $this->paginator = $this->createMock(Paginator::class);
        $this->pageNumberFinder = $this->createMock(PageNumberFinder::class);
        $this->frontendConfig = $this->createMock(FrontendConfig::class);
        $this->resourceFileStorage = $this->createMock(ResourceFileStorage::class);
        $this->fileSystemDriver = $this->createMock(FileSystemDriver::class);
        $this->extension = new TwigFrontendExtension(
            $this->createMock(RequestStack::class),
            $this->resourceKindRepository,
            $this->metadataRepository,
            $this->paginator,
            $this->frontendConfig,
            $this->pageNumberFinder,
            $this->resourceFileStorage,
            $this->fileSystemDriver
        );
    }

    public function testCheckingFacetFilter() {
        foreach ([
                     ['kindId', 1, ['kindId' => ['1']], true],
                     ['kindId', '1', ['kindId' => [1]], true],
                     ['kindId', 1, ['kindId' => ['1', '2']], true],
                     ['kindId', 1, ['kindId' => ['2']], false],
                     ['kindId', '1', ['kindId' => ['11']], false],
                     ['kindId', 1, ['123' => ['1']], false],
                     ['123', 1, ['123' => ['1']], true],
                 ] as $testCase) {
            list($aggregationName, $filterValue, $filters, $expected) = $testCase;
            $this->assertEquals(
                $expected,
                $this->extension->isFilteringByFacet($aggregationName, $filterValue, $filters)
            );
        }
    }

    public function testBibtexEscaping() {
        foreach ([
                     ['{ala}', '"\{ala\}"'],
                     ['"a$a"', '"\"a\$a\""'],
                     ['a\a', '"a\\\\a"'],
                     ['a\$\"{}a', '"a\\\\\$\\\\\"\{\}a"'],
                 ] as $testCase) {
            list($initialValue, $expectedValue) = $testCase;
            $this->assertEquals(
                $expectedValue,
                $this->extension->bibtexEscape($initialValue)
            );
        }
    }

    public function testMatchingUrls() {
        $requestStack = $this->createMock(RequestStack::class);
        foreach ([
                     [null, ['/'], false],
                     [false, ['/#'], false],
                     ['/resources', [], false],
                     ['/', ['/#', '/search'], true],
                     ['/search/1', ['/#', '/search'], true],
                     ['/search/1/2', ['/#', '/search'], true],
                     ['/resources', ['/#', '/search'], false],
                     ['/resources/123', ['/resources/123'], true],
                     ['/resources/1234', ['/resources/123'], true],
                     ['/resources/123/4', ['/resources/123'], true],
                     ['/resources', ['/resources/123'], false],
                 ] as $testCase) {
            list($requestUri, $urls, $expected) = $testCase;
            $requestStack->expects($this->atLeastOnce())
                ->method('getCurrentRequest')
                ->willReturnCallback(
                    function () use (&$requestUri) {
                        return $requestUri ? Request::create($requestUri) : null;
                    }
                );
            $extension = new TwigFrontendExtension(
                $requestStack,
                $this->resourceKindRepository,
                $this->metadataRepository,
                $this->paginator,
                $this->createMock(FrontendConfig::class),
                $this->createMock(PageNumberFinder::class),
                $this->resourceFileStorage,
                $this->fileSystemDriver
            );
            $this->assertEquals(
                $expected,
                $extension->urlMatches(...$urls)
            );
        }
    }

    public function testBasename() {
        $this->assertEquals(
            'file.gif',
            $this->extension->basename('common/file.gif')
        );
    }

    public function testMatchingSearchHitsWithPageNumbers() {
        $this->pageNumberFinder->method('matchSearchHitsWithPageNumbers')->willReturn(
            [
                [
                    PageNumberFinder::PAGE_NUMBER => 29,
                    PageNumberFinder::HIGHLIGHT =>
                        "Some <em>highlighted</em> stuff",
                ],
                [
                    PageNumberFinder::PAGE_NUMBER => 57,
                    PageNumberFinder::HIGHLIGHT =>
                        "<em>Highlights</em> and some more <em>highlights</em> after",
                ],
                [
                    PageNumberFinder::PAGE_NUMBER => 101,
                    PageNumberFinder::HIGHLIGHT =>
                        "Here we have <em>highlights</em> and some more <em>highlights</em> to <em>highlight</em> this test",
                ],
            ]
        );
        $expectedResult = [
            ['highlight' => '<em>highlighted</em>', 'pageNumber' => 29],
            ['highlight' => '<em>Highlights</em> and some more <em>highlights</em>', 'pageNumber' => 57],
            ['highlight' => '<em>highlights</em> and some more <em>highlights</em> to <em>highlight</em>', 'pageNumber' => 101],
        ];
        $resource = $this->createResourceMock(1, $this->createResourceKindMock());
        $this->assertEquals($expectedResult, $this->extension->matchSearchHitsWithPageNumbers($resource, MetadataControl::FILE, [], []));
    }

    public function testFilteringDisplayedMetadata() {
        $metadataToDisplay = [1, 2, 'obrazki'];
        $groupedMetadataList = [
            'basic' => [$this->createMetadataMock(1), $this->createMetadataMock(2), $this->createMetadataMock(456)],
            'cms' => [$this->createMetadataMock(3, null, null, [], 'books', [], 'obrazki'), $this->createMetadataMock(678)],
        ];
        $expectedResult = [
            'basic' => [$this->createMetadataMock(1), $this->createMetadataMock(2)],
            'cms' => [$this->createMetadataMock(3, null, null, [], 'books', [], 'obrazki')],
        ];
        $filteredMetadata = $this->extension->filterMetadataToDisplay($groupedMetadataList, $metadataToDisplay);
        $this->assertEquals($expectedResult, $filteredMetadata);
    }

    public function testFilteringDisplayedMetadataRemovesEmptyGroups() {
        $metadataToDisplay = [1, 2];
        $groupedMetadataList = [
            'basic' => [$this->createMetadataMock(1), $this->createMetadataMock(2), $this->createMetadataMock(456)],
            'cms' => [$this->createMetadataMock(678)],
        ];
        $expectedResult = [
            'basic' => [$this->createMetadataMock(1), $this->createMetadataMock(2)],
        ];
        $filteredMetadata = $this->extension->filterMetadataToDisplay($groupedMetadataList, $metadataToDisplay);
        $this->assertEquals($expectedResult, $filteredMetadata);
    }

    public function testListFilesFromDirectoryMetadata() {
        $resource = $this->createResourceMock(1, null, [1 => 'testFolder']);
        $metadata = $this->createMetadataMock(1, null, MetadataControl::DIRECTORY());
        $this->resourceFileStorage->method('getDirectoryContents')->willReturn(['testFolder/fileA', 'testFolder/fileB']);
        $actualFiles = $this->extension->metadataFiles($resource, $metadata);
        $this->assertEquals(['testFolder/fileA', 'testFolder/fileB'], $actualFiles);
    }

    public function testListFilesFromFileMetadata() {
        $resource = $this->createResourceMock(1, null, [1 => ['fileA', 'fileB']]);
        $metadata = $this->createMetadataMock(1, null, MetadataControl::FILE());
        $this->metadataRepository->method('findByNameOrId')->willReturn($metadata);
        $actualFiles = $this->extension->metadataFiles($resource, $metadata);
        $this->assertEquals(['fileA', 'fileB'], $actualFiles);
    }

    public function testListFilesFilteringByExtension() {
        $resource = $this->createResourceMock(1, null, [1 => ['fileGood.txt', 'fileBad.png']]);
        $metadata = $this->createMetadataMock(1, null, MetadataControl::FILE());
        $this->metadataRepository->method('findByNameOrId')->willReturn($metadata);
        $actualFiles = $this->extension->metadataFiles($resource, $metadata, ['txt']);
        $this->assertEquals(['fileGood.txt'], $actualFiles);
    }
}
