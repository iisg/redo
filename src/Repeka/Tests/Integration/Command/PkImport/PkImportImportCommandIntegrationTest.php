<?php
namespace Repeka\Tests\Integration\Command\PkImport;

use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindUpdateCommand;
use Repeka\Tests\Integration\Traits\FixtureHelpers;

class PkImportImportCommandIntegrationTest extends AbstractPkImportIntegrationTest {
    use FixtureHelpers;

    /** @before */
    public function init(): void {
        $this->loadAllFixtures();
        $oldIdMetadata = $this->createSimpleMetadata('old_id', MetadataControl::INTEGER(), 'books');
        $rk = $this->getPhpBookResource()->getKind();
        $this->handleCommandBypassingFirewall(
            new ResourceKindUpdateCommand(
                $rk,
                $rk->getLabel(),
                array_merge($rk->getMetadataList(), [$oldIdMetadata]),
                $rk->getDisplayStrategies(),
                $rk->getWorkflow()
            )
        );
    }

    public function testImportsResources() {
        $countBefore = $this->getResourceRepository()->count([]);
        $this->import();
        $countAfter = $this->getResourceRepository()->count([]);
        $this->assertEquals($countBefore + 2, $countAfter);
    }

    public function testImportsTitle() {
        $this->import();
        $resource = $this->findResourceByContents(['Tytuł' => 'scuffing']);
        $this->assertEquals(
            $resource->getValues($this->findMetadataByName('Tytuł')),
            ['Rozwój zużycia typu scuffing w ruchu oscylacyjnym']
        );
    }

    public function testImportsRelation() {
        $this->import();
        $resource = $this->findResourceByContents(['Zobacz też' => 77034]);
        $this->assertEquals(
            $resource->getValues($this->findMetadataByName('Tytuł')),
            ['Rozwój zużycia typu scuffing w ruchu oscylacyjnym']
        );
    }

    public function testImportsOldResourceId() {
        $this->import();
        $resource = $this->findResourceByContents(['Old ID' => 76968]);
        $this->assertEquals(
            $resource->getValues($this->findMetadataByName('Tytuł')),
            ['Rozwój zużycia typu scuffing w ruchu oscylacyjnym']
        );
    }

    private function import() {
        $this->importFile('sample-resources-export', $this->getPhpBookResource()->getKind());
    }
}
