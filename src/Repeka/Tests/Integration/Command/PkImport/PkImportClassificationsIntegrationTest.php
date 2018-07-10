<?php
namespace Repeka\Tests\Integration\Command\PkImport;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\UseCase\Resource\ResourceTopLevelPathQuery;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindCreateCommand;
use Repeka\Tests\Integration\Traits\FixtureHelpers;

class PkImportClassificationsIntegrationTest extends AbstractPkImportIntegrationTest {
    use FixtureHelpers;

    /** @var ResourceKind */
    private $classificationRk;

    /** @before */
    public function init(): void {
        $nameMetadata = $this->createSimpleMetadata('nazwa', MetadataControl::TEXT(), 'dictionaries');
        $oldIdMetadata = $this->createSimpleMetadata('old_id', MetadataControl::INTEGER(), 'dictionaries');
        $this->classificationRk = $this->handleCommandBypassingFirewall(
            new ResourceKindCreateCommand(['PL' => 'Klasyfikacja', 'EN' => 'Classification'], [$nameMetadata, $oldIdMetadata])
        );
    }

    public function testImportsClassifications() {
        $this->assertEquals(0, $this->getResourceRepository()->count(['kind' => $this->classificationRk]));
        $this->import();
        $this->assertEquals(36, $this->getResourceRepository()->count(['kind' => $this->classificationRk]));
    }

    public function testImportsOldId() {
        $this->import();
        $classificationByName = $this->findResourceByContents(['nazwa' => 'Teoria poznania']);
        $classificationByOldId = $this->findResourceByContents(['old_id' => 205]);
        $this->assertNotNull($classificationByName);
        $this->assertEquals($classificationByName, $classificationByOldId);
    }

    public function testMapsRelationships() {
        $this->import();
        $historiaFilozofii = $this->findResourceByContents(['nazwa' => 'Historia filozofii']);
        $pathToTop = $this->handleCommandBypassingFirewall(new ResourceTopLevelPathQuery($historiaFilozofii, SystemMetadata::PARENT));
        $this->assertTrue($historiaFilozofii->hasParent());
        $this->assertCount(1, $pathToTop);
        $nameMetadata = $this->findMetadataByName('nazwa', 'dictionaries');
        $this->assertEquals(['Filozofia'], $pathToTop[0]->getValues($nameMetadata));
    }

    public function testMapsMultipleRelationships() {
        $this->import();
        $historiaReligioznawstwa = $this->findResourceByContents(['nazwa' => 'Historia i stan współczesny religioznawstwa']);
        $pathToTop = $this->handleCommandBypassingFirewall(new ResourceTopLevelPathQuery($historiaReligioznawstwa, SystemMetadata::PARENT));
        $this->assertTrue($historiaReligioznawstwa->hasParent());
        $this->assertCount(2, $pathToTop);
        $nameMetadata = $this->findMetadataByName('nazwa', 'dictionaries');
        $this->assertEquals(['Metareligioznawstwo (nauka o religioznawstwie)'], $pathToTop[0]->getValues($nameMetadata));
        $this->assertEquals(['Religioznawstwo. Religie'], $pathToTop[1]->getValues($nameMetadata));
    }

    private function import() {
        $this->importFile('index-klasyfikacja_pkt', $this->classificationRk);
    }
}
