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

class TwigFrontendExtensionTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceKindRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $resourceKindRepository;
    /** @var TwigFrontendExtension */
    private $extension;
    /** @var Paginator|\PHPUnit_Framework_MockObject_MockObject */
    private $paginator;

    /** @before */
    public function init() {
        $this->resourceKindRepository = $this->createMock(ResourceKindRepository::class);
        $this->paginator = $this->createMock(Paginator::class);
        $this->frontendConfig = $this->createMock(FrontendConfig::class);
        $this->extension = new TwigFrontendExtension(
            $this->createMock(RequestStack::class),
            $this->resourceKindRepository,
            $this->paginator,
            $this->createMock(FrontendConfig::class),
            $this->createMock(PageNumberFinder::class)
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
                $this->createMock(PageNumberFinder::class)
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
}
