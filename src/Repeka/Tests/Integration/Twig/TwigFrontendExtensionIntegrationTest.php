<?php
namespace Repeka\Tests\Integration\Twig;

use Repeka\Application\Twig\TwigResourceDisplayStrategyEvaluator;
use Repeka\Domain\Entity\ResourceContents as RC;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Service\ResourceDisplayStrategyEvaluator;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;

class TwigFrontendExtensionIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;
    /** @var TwigResourceDisplayStrategyEvaluator */
    private $evaluator;
    /** @var ResourceEntity */
    private $phpBookResource;

    /** @before */
    public function before() {
        $this->loadAllFixtures();
        $this->evaluator = $this->container->get(ResourceDisplayStrategyEvaluator::class);
        $this->phpBookResource = $this->getPhpBookResource();
    }

    /*
     * This cannot be a parametrized test because we can't know the Title's metadata id at the time of data provisioning.
     * And this way is much faster because we run the fixtures only once.
     */
    public function testRendering() {
        foreach ($this->renderingExamples() as $testCase) {
            if (count($testCase) == 2) {
                $testCase[] = $this->phpBookResource;
            }
            [$template, $expectedOutput, $subject] = $testCase;
            $rendered = $this->evaluator->render($subject, $template);
            $this->assertEquals($expectedOutput, $rendered, 'Failed for template: ' . $template);
        }
    }

    private function renderingExamples() {
        $phpBookId = $this->getPhpBookResource()->getId();
        $phpMysqlBookId = $this->findResourceByContents(['Tytuł' => 'PHP i MySQL'])->getId();
        // @codingStandardsIgnoreStart
        // @formatter:off because indentation makes config structure way clearer
        return [
            ["{{ r|ftsContentsToResource|m(1) }}", "Potop, Powódź", [1 => [['value_text' => 'Potop'], ['value_display-strategy' => 'Powódź']]]], // can render directly from ES hits?
            ["{{ r|m('Liczba stron')|merge(r($phpMysqlBookId)|m('Liczba stron'))|sum }}", "1741"],// sum pages
            ["{{ r|m(1)|m('Liczba stron')|sum }}", "1741", RC::fromArray([1 => [$phpBookId, $phpMysqlBookId]])],// sum pages by associations
            ["{{ resourceKind(-1).label.PL }}", "user"],// resource kind fetching
        ];
        // @formatter:on
        // @codingStandardsIgnoreEnd
    }
}
