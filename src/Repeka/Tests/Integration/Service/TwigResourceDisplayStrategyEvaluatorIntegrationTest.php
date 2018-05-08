<?php
namespace Repeka\Tests\Integration;

use Repeka\Application\Service\DisplayStrategies\TwigResourceDisplayStrategyEvaluator;
use Repeka\Domain\Entity\ResourceContents as RC;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Service\ResourceDisplayStrategyEvaluator;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;

class TwigResourceDisplayStrategyEvaluatorIntegrationTest extends IntegrationTestCase {
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
        $tId = $this->findMetadataByName('Tytuł')->getId();
        $phpBookId = $this->getPhpBookResource()->getId();
        $phpMysqlBookId = $this->findResourceByContents(['Tytuł' => 'PHP i MySQL'])->getId();
        return [
            ['', "ID $phpBookId"],
            // empty text
            ['  ', "ID $phpBookId"],
            // blank text
            ['Hello world', 'Hello world'], // simple text
            ['Hello {{ r.id }}', "Hello $phpBookId"],
            // get id
            ["{{ r.values($tId) | join(', ') }}", "PHP - to można leczyć!"], // get metadata values
            ["{{ r.values($tId)|first }}", "PHP - to można leczyć!"], // get metadata value
            ["{{ r|m($tId) }}", "PHP - to można leczyć!"], // get metadata values without join
            ["{{ r|m($tId)[0] }}", "PHP - to można leczyć!"], // get metadata values without join
            ["{{ r|m($tId)[1] }}", ""], // get metadata values without join
            ['{{ r.values(-66)|first }}', ""], // get not existing metadata
            ["{{ r.values(m('Tytuł'))|first }}", "PHP - to można leczyć!"], // fetch metadata by name
            ["{{ r(1).values(-2)|first }} i {{ r.values($tId)|first }}", "admin i PHP - to można leczyć!"], // fetch resources
            ["{{ r(r.values(m('Nadzorujący'))|first).values(m('Username'))|first }}", "budynek"], // supervisor's username - tough way
            ["{{ r|m('Nadzorujący')|first|r|m('Username')|first }}", "budynek"], // supervisor's username - with filters
            ["{{ r|m('Nadzorujący')|r|m('Username') }}", "budynek"], // can handle many values?
            ["{{ r|m('Nadzorujący')|m('Username') }}", "budynek"], // can skip resource fetching?
            ["{{ r|m(1) }}", "Potop, Powódź", RC::fromArray([1 => ['Potop', 'Powódź']])], // can render directly from resource contents?
            ["{{ r|m(1)|m('Username') }}", "admin, budynek, tester", RC::fromArray([1 => [1, 2, 3]])], // many associations
            ["{{ r|m('Liczba stron')|merge(r($phpMysqlBookId)|m('Liczba stron'))|sum }}", "1741"],
            // sum pages
            ["{{ r|m(1)|m('Liczba stron')|sum }}", "1741", RC::fromArray([1 => [$phpBookId, $phpMysqlBookId]])],
            // sum pages by associations
            ["{{ r|m$tId }}", "PHP - to można leczyć!"], // dynamic filter
            ["{% if r|m1|first %}TAK{% else %}NIE{% endif %}", "TAK", RC::fromArray([1 => true])], // bool true
            ["{% if r|m1|first %}TAK{% else %}NIE{% endif %}", "NIE", RC::fromArray([1 => false])], // bool false
            ["{% for v in r|m1 %}- {{ v }}{% endfor %}", "- A- B", RC::fromArray([1 => ['A', 'B']])], // bool false
            ["{{ r|m1 }}", "", RC::empty()], // empty metadata
            ["{{ r|mUnicorn }}", ""], // unknown metadata name
            ["{{ r|m$tId|mUsername }}", "", RC::fromArray([1 => [1500100900]])], // unknown resource in association
            ["{{ r }}", "Object of class Repeka\Domain\Entity\ResourceEntity could not be converted to string"], // runtime error
            ["{{ r|m }}", 'Please specify metadata by choosing one of the following syntax: m1, mName, m(1), m("Name")'], // runtime error
            ["{{ r|m-2 }}", 'Please specify metadata by choosing one of the following syntax: m1, mName, m(1), m("Name")'], // runtime error
            ["{{ r|r }}", 'Given resource ID is not valid.'], // runtime error
            ["{{ r('ala') }}", 'Given resource ID is not valid.'], // runtime error
            ["{{ r", 'Unexpected token "end of template" of value "" ("end of print statement" expected).'], // syntax error (compile error)
        ];
    }

    public function testShowingHowCoolItIs() {
        $rendered = $this->evaluator->render(
            $this->phpBookResource,
            "Zasób #{{r.id}}: {{r|mTytuł}} Nadzorujący: {{r|mNadzorujący|mUsername}}, Skanista: {{r|mSkanista|mUsername}}."
            . " {%if r|m('Czy ma twardą okładkę?')|first %}Twarda{% else %}Miękka{% endif %} okładka"
        );
        $id = $this->phpBookResource->getId();
        $this->assertEquals("Zasób #$id: PHP - to można leczyć! Nadzorujący: budynek, Skanista: skaner. Twarda okładka", $rendered);
    }
}
