<?php
namespace Repeka\Tests\Integration\Twig;

use Repeka\Application\Twig\TwigResourceDisplayStrategyEvaluator;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceContents as RC;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Service\ResourceDisplayStrategyEvaluator;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommand;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindUpdateCommand;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;

class TwigResourceDisplayStrategyEvaluatorIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;
    /** @var TwigResourceDisplayStrategyEvaluator */
    private $evaluator;
    /** @var ResourceEntity */
    private $phpBookResource;
    /** @var Metadata */
    private $titleMetadata;

    /** @before */
    public function before() {
        $this->loadAllFixtures();
        $this->evaluator = $this->container->get(ResourceDisplayStrategyEvaluator::class);
        $this->phpBookResource = $this->getPhpBookResource();
        $this->titleMetadata = $this->findMetadataByName('Tytuł');
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
        $tId = $this->titleMetadata->getId();
        $phpBookId = $this->getPhpBookResource()->getId();
        // @codingStandardsIgnoreStart
        // @formatter:off because indentation makes config structure way clearer
        return [
            ['', "ID $phpBookId"],
            // empty text
            ['  ', "ID $phpBookId"],
            // blank text
            ['Hello world', 'Hello world'], // simple text
            ['Hello {{ r.id }}', "Hello $phpBookId"],
            ['Hello {{ resource.id }}', "Hello $phpBookId"],
            // get id
            ["{{ r.values($tId) | join(', ') }}", "PHP - to można leczyć!"], // get metadata values
            ["{{ r.values($tId)|first }}", "PHP - to można leczyć!"], // get metadata value
            ["{{ r|m($tId) }}", "PHP - to można leczyć!"], // get metadata values without join
            ["{{ r|m$tId }}", "PHP - to można leczyć!"], // get metadata values without join
            ["{{ r|metadata$tId }}", "PHP - to można leczyć!"], // get metadata values without join
            ["{{ resource|metadata($tId) }}", "PHP - to można leczyć!"], // get metadata values without join
            ["{{ r|m($tId)[0] }}", "PHP - to można leczyć!"], // get metadata values without join
            ["{{ r|m($tId)[1] }}", ""], // get metadata values without join
            ['{{ r.values(-66)|first }}', ""], // get not existing metadata
            ["{{ r.values(m('Tytuł'))|first }}", "PHP - to można leczyć!"], // fetch metadata by name
            ["{{ r(1).values(-2)|first }} i {{ r.values($tId)|first }}", "admin i PHP - to można leczyć!"], // fetch resources
            ["{{ resource(1).values(-2)|first }} i {{ r.values($tId)|first }}", "admin i PHP - to można leczyć!"], // fetch resources
            ["{{ r(r.values(m('Nadzorujący'))|first).values(m('Username'))|first }}", "budynek"], // supervisor's username - tough way
            ["{{ r|m('Nadzorujący')|first|r|m('Username')|first }}", "budynek"], // supervisor's username - with filters
            ["{{ r|m('Nadzorujący')|r|m('Username') }}", "budynek"], // can handle many values?
            ["{{ r|m('Nadzorujący')|m('Username') }}", "budynek"], // can skip resource fetching?
            ["{{ r|m(1) }}", "Potop, Powódź", RC::fromArray([1 => ['Potop', 'Powódź']])], // can render directly from resource contents?
            ["{% for title in r|m1 %}{{ loop.index }}. {{ title }}{%endfor%}", "1. Potop2. Powódź", RC::fromArray([1 => ['Potop', 'Powódź']])], // loop.index
            ["{{ r|m(1)|m('Username') }}", "admin, budynek, tester", RC::fromArray([1 => [1, 2, 3]])], // many associations
            ["{{ r|m$tId }}", "PHP - to można leczyć!"], // dynamic filter
            ["{% if r|m1|first.value %}TAK{% else %}NIE{% endif %}", "TAK", RC::fromArray([1 => true])], // bool true
            ["{% if r|m1|first.value %}TAK{% else %}NIE{% endif %}", "NIE", RC::fromArray([1 => false])], // bool false
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
            ["{{ '{{ r.id }}' | evaluate }}", $phpBookId], // eval extension
            ["{{ r|mUrl|first }}", 'http://google.pl'], // top value of metadata with submetadata
            ["{{ r|mUrl|first|submetadataUrlLabel }}", 'Tam znajdziesz więcej'], // submetadata of first value
            ["{{ r|mUrl|submetadataUrlLabel }}", 'Tam znajdziesz więcej, Tam znajdziesz więcej ale inni nie dowiedzą się, że interesujesz się PHP'], // array of all submetadata values
            ["{{ r|mUrl|submetadataUrlLabel|first }}", 'Tam znajdziesz więcej'], // first submetadata value
            ["{{ r|mUrl|subUrlLabel|first }}", 'Tam znajdziesz więcej'], // sub shorthand
            ['{%for url in r|mUrl%}<a href="{{url}}">{{url|submetadataUrlLabel}}</a>{%endfor%}', '<a href="http://google.pl">Tam znajdziesz więcej</a><a href="https://duckduckgo.com">Tam znajdziesz więcej ale inni nie dowiedzą się, że interesujesz się PHP</a>'], // links generation
        ];
        // @formatter:on
        // @codingStandardsIgnoreEnd
    }

    public function testShowingHowCoolItIs() {
        $rendered = $this->evaluator->render(
            $this->phpBookResource,
            "Zasób #{{r.id}}: {{r|mTytuł}} Nadzorujący: {{r|mNadzorujący|mUsername}}, Skanista: {{r|mSkanista|mUsername}}."
            . " {%if r|m('Czy ma twardą okładkę?')|first.value %}Twarda{% else %}Miękka{% endif %} okładka"
        );
        $id = $this->phpBookResource->getId();
        $this->assertEquals("Zasób #$id: PHP - to można leczyć! Nadzorujący: budynek, Skanista: skaner. Twarda okładka", $rendered);
    }

    public function testRenderingBreadcrumb() {
        $phpBook = $this->getPhpBookResource();
        $bookRk = $phpBook->getKind();
        $rkMetadataList = array_filter(
            $bookRk->getMetadataList(),
            function (Metadata $metadata) {
                return $metadata->getId() != SystemMetadata::PARENT;
            }
        );
        $rkMetadataList[] = SystemMetadata::PARENT()->toMetadata()->withOverrides(
            ['constraints' => ['resourceKind' => [$bookRk->getId()]]]
        );
        $this->handleCommandBypassingFirewall(
            new ResourceKindUpdateCommand($bookRk, $bookRk->getLabel(), $rkMetadataList)
        );
        $firstLevelChild = $this->handleCommandBypassingFirewall(
            new ResourceCreateCommand(
                $bookRk,
                ResourceContents::fromArray([$this->titleMetadata->getId() => 'First level', SystemMetadata::PARENT => $phpBook->getId()])
            )
        );
        $secondLevelChild = $this->handleCommandBypassingFirewall(
            new ResourceCreateCommand(
                $bookRk,
                ResourceContents::fromArray(
                    [$this->titleMetadata->getId() => 'Second level', SystemMetadata::PARENT => $firstLevelChild->getId()]
                )
            )
        );
        $thirdLevelChild = $this->handleCommandBypassingFirewall(
            new ResourceCreateCommand(
                $bookRk,
                ResourceContents::fromArray(
                    [$this->titleMetadata->getId() => 'Third level', SystemMetadata::PARENT => $secondLevelChild->getId()]
                )
            )
        );
        $breadcrumbTemplate = <<<BR
{% set parentTitles = [] %}
{% set parent = r | mParent | first %}
{% for i in 0..5 if parent %}
    {% set parentTitles = [parent | mTytul] | merge(parentTitles) %}
    {% set parent = parent | mParent | first %}
{% endfor %}
{% for title in parentTitles %}{{ title }} > {% endfor %}{{ r | mTytul }}
BR;
        $rendered = trim($this->evaluator->render($thirdLevelChild, $breadcrumbTemplate));
        $this->assertEquals('PHP - to można leczyć! > First level > Second level > Third level', $rendered);
    }
}
