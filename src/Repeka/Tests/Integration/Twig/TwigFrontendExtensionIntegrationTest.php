<?php
namespace Repeka\Tests\Integration\Twig;

use Repeka\Application\Twig\TwigResourceDisplayStrategyEvaluator;
use Repeka\Domain\Entity\ResourceContents as RC;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Service\ResourceDisplayStrategyEvaluator;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;

/** @small */
class TwigFrontendExtensionIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;
    /** @var TwigResourceDisplayStrategyEvaluator */
    private $evaluator;
    /** @var ResourceEntity */
    private $phpBookResource;

    protected function initializeDatabaseForTests() {
        $this->loadAllFixtures();
    }

    /** @before */
    public function loadEntities() {
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
            ["{{ '123'|wrap('a','z') }}", "a123z"],// wrapping string
            ["{{ 123|wrap('a','z') }}", "a123z"],// wrapping number
            ["{{ '123'|split('')|wrap('a','z')|join }}", "a1za2za3z"],// wrapping array of strings
        ];
        // @formatter:on
        // @codingStandardsIgnoreEnd
    }

    //This test can use only publicly visible resources due to visibility filtering
    public function testFetchingResources() {
        $couldUseWebpackId = $this->findResourceByContents(['tytul' => 'Mogliśmy użyć Webpacka'])->getId();
        $phpBookId = $this->getPhpBookResource()->getId();
        $ebooksId = $this->findResourceByContents(['nazwa_kategorii' => 'E-booki'])->getId();
        $testCases = [
            ['{}', [$phpBookId, $ebooksId, $couldUseWebpackId], [100]],
            ['{parentId: null}', [$ebooksId, $phpBookId], [$couldUseWebpackId]],
            ['{resourceClass: "books"}', [$phpBookId, $couldUseWebpackId], [1, 2]],
            ["{resourceKindIds: [{$this->getPhpBookResource()->getKind()->getId()}]}", [$phpBookId, $couldUseWebpackId], [1, 2, $ebooksId]],
        ];
        foreach ($testCases as $testCase) {
            list($filters, $expectedIds, $notExpectedIds) = $testCase;
            $rendered = $this->evaluator->render(
                $this->createMock(ResourceEntity::class),
                '{%for resource in resources(' . $filters . ')%}{{ resource.id }},{%endfor%}'
            );
            $fetchedIds = explode(',', $rendered);
            foreach ($expectedIds as $expectedId) {
                $this->assertContains($expectedId, $fetchedIds);
            }
            foreach ($notExpectedIds as $notExpectedId) {
                $this->assertNotContains($notExpectedId, $fetchedIds);
            }
        }
    }
}
