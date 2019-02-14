<?php
namespace Repeka\Plugins\Redo\Tests\Integration\PkImport;

use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\UseCase\Resource\ResourceUpdateContentsCommand;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindUpdateCommand;
use Repeka\Tests\Integration\Traits\FixtureHelpers;

/** @small */
class PkImportImportCommandIntegrationTest extends AbstractPkImportIntegrationTest {
    use FixtureHelpers;

    public function initializeDatabaseForTests(): void {
        $this->loadAllFixtures();
        $oldIdMetadata = $this->createSimpleMetadata('old_id', MetadataControl::INTEGER(), 'books');
        $rk = $this->getPhpBookResource()->getKind();
        $this->handleCommandBypassingFirewall(
            new ResourceKindUpdateCommand(
                $rk,
                $rk->getLabel(),
                array_merge($rk->getMetadataList(), [$oldIdMetadata]),
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

    /** @depends testImportsResources */
    public function testImportTwiceDoesNotDuplicateResources() {
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

    public function testAddsValueIfDoesNotExist() {
        $this->import();
        $resourceWithExistingValue = $this->findResourceByContents(['Old ID' => 76968]);
        $this->assertEquals(
            $resourceWithExistingValue->getValues($this->findMetadataByName('Nadzorujacy')),
            ['190']
        );
        $resourceWithDefaultValue = $this->findResourceByContents(['Old ID' => 76805]);
        $this->assertEquals(
            $resourceWithDefaultValue->getValues($this->findMetadataByName('Nadzorujacy')),
            ['12345']
        );
    }

    public function testAddsDefaultValue() {
        $this->import();
        $resource = $this->findResourceByContents(['Old ID' => 76805]);
        $this->assertEquals(
            $resource->getValues($this->findMetadataByName('Skanista')),
            ['12345', '23456']
        );
        $resourceWithDefaultValue = $this->findResourceByContents(['Old ID' => 76805]);
        $this->assertEquals(
            $resourceWithDefaultValue->getValues($this->findMetadataByName('Nadzorujacy')),
            ['12345']
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

    public function testImportTwiceOverwritesExistingMetadata() {
        $this->import();
        $resource = $this->findResourceByContents(['Tytuł' => 'scuffing']);
        $newContents = $resource->getContents()->withReplacedValues($this->findMetadataByName('Tytuł')->getId(), 'ALA MA KOTA');
        $this->handleCommandBypassingFirewall(new ResourceUpdateContentsCommand($resource, $newContents));
        $this->import();
        $resource = $this->getResourceRepository()->findOne($resource->getId());
        $this->assertEquals(
            $resource->getValues($this->findMetadataByName('Tytuł')),
            ['Rozwój zużycia typu scuffing w ruchu oscylacyjnym']
        );
    }

    private function import() {
        $this->importFile('sample-resources-export', $this->getPhpBookResource()->getKind());
    }
}
