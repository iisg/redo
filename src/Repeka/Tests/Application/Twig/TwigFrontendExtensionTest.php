<?php
namespace Repeka\Tests\Application\Upload;

use Repeka\Application\Twig\TwigFrontendExtension;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Tests\Traits\StubsTrait;

class TwigFrontendExtensionTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var TwigFrontendExtension */
    private $extension;

    /** @before */
    public function init() {
        $this->extension = new TwigFrontendExtension($this->createMock(ResourceKindRepository::class));
    }

    public function testCheckingFacetFilter() {
        foreach ([
                     ['kindId', 1, ['kindId' => '1'], true],
                     ['kindId', '1', ['kindId' => 1], true],
                     ['kindId', 1, ['kindId' => '1,2'], true],
                     ['kindId', 1, ['kindId' => '2'], false],
                     ['kindId', '1', ['kindId' => '11'], false],
                     ['kindId', 1, ['123' => '1'], false],
                     ['123', 1, ['123' => '1'], true],
                 ] as $testCase) {
            list($aggregationName, $filterValue, $filters, $expected) = $testCase;
            $this->assertEquals(
                $expected,
                $this->extension->isFilteringByFacet($aggregationName, $filterValue, $filters)
            );
        }
    }

    public function testGeneratingFacetParam() {
        foreach ([
                     ['kindId', 1, ['kindId' => '1'], []],
                     ['kindId', 1, ['kindId' => '2'], ['kindId' => '1,2']],
                     ['kindId', 2, ['kindId' => '1'], ['kindId' => '1,2']],
                     ['kindId', 2, ['123' => '2'], ['123' => '2', 'kindId' => '2']],
                     ['kindId', 2, ['123' => '2', 'kindId' => 2], ['123' => '2']],
                     ['kindId', 2, ['123' => '2', 'kindId' => '1,2'], ['kindId' => '1', '123' => '2']],
                 ] as $testCase) {
            list($aggregationName, $filterValue, $filters, $expected) = $testCase;
            $this->assertEquals(
                $expected,
                $this->extension->ftsFacetFilterParam($aggregationName, $filterValue, $filters)
            );
        }
    }
}
