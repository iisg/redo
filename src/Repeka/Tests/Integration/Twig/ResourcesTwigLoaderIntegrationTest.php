<?php
namespace Repeka\Tests\Integration\Twig;

use Repeka\Application\Twig\ResourcesTwigLoader;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\UseCase\Resource\ResourceEvaluateDisplayStrategiesCommand;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;

class ResourcesTwigLoaderIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;

    /** @var ResourceEntity */
    private $aboutPage;
    /** @var Metadata */
    private $renderedMetadata;
    /** @var ResourcesTwigLoader */
    private $loader;

    /** @before */
    public function before() {
        $this->loadAllFixtures();
        $pageTitleMetadata = $this->findMetadataByName('Tytuł strony', 'cms');
        $this->aboutPage = $this->findResourceByContents([$pageTitleMetadata->getId() => 'O projekcie']);
        $this->renderedMetadata = $this->findMetadataByName('Wyrenderowana treść strony', 'cms');
        $this->loader = $this->container->get(ResourcesTwigLoader::class);
    }

    public function testRenderingFromFile() {
        $this->handleCommandBypassingFirewall(new ResourceEvaluateDisplayStrategiesCommand($this->aboutPage));
        $contents = $this->aboutPage->getValues($this->renderedMetadata)[0]->getValue();
        $this->assertContains('<html lang="pl">', $contents);
    }

    public function testRenderingAfterImport() {
        $this->executeCommand('repeka:templates:import redo');
        $this->handleCommandBypassingFirewall(new ResourceEvaluateDisplayStrategiesCommand($this->aboutPage));
        $contents = $this->aboutPage->getValues($this->renderedMetadata)[0]->getValue();
        $this->assertContains('<html lang="pl">', $contents);
    }
}
