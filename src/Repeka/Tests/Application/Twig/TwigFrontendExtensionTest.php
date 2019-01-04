<?php
namespace Repeka\Tests\Application\Upload;

use Repeka\Application\Elasticsearch\PageNumberFinder;
use Repeka\Application\Twig\FrontendConfig;
use Repeka\Application\Twig\Paginator;
use Repeka\Application\Twig\TwigFrontendExtension;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Tests\Traits\StubsTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

class TwigFrontendExtensionTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceKindRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $resourceKindRepository;
    /** @var TwigFrontendExtension */
    private $extension;
    /** @var Paginator|\PHPUnit_Framework_MockObject_MockObject */
    private $paginator;
    /** @var PageNumberFinder|\PHPUnit_Framework_MockObject_MockObject */
    private $pageNumberFinder;

    /** @before */
    public function init() {
        $this->resourceKindRepository = $this->createMock(ResourceKindRepository::class);
        $this->paginator = $this->createMock(Paginator::class);
        $this->pageNumberFinder = $this->createMock(PageNumberFinder::class);
        $this->extension = new TwigFrontendExtension(
            $this->createMock(RequestStack::class),
            $this->resourceKindRepository,
            $this->paginator,
            $this->createMock(FrontendConfig::class),
            $this->pageNumberFinder
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
                $this->paginator,
                $this->createMock(FrontendConfig::class),
                $this->createMock(PageNumberFinder::class),
                $this->createMock(AccessDecisionManagerInterface::class)
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
            "<u>str. 29:</u> <em>highlighted</em>",
            "<u>str. 57:</u> <em>Highlights</em> and some more <em>highlights</em>",
            "<u>str. 101:</u> <em>highlights</em> and some more <em>highlights</em> to <em>highlight</em>",
        ];
        $resource = $this->createResourceMock(1, $this->createResourceKindMock());
        $this->assertEquals($expectedResult, $this->extension->matchSearchHitsWithPageNumbers($resource, [], []));
    }
}
