<?php
namespace Repeka\Plugins\Redo\Tests\Integration\PkImport;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\UseCase\Metadata\MetadataCreateCommand;
use Repeka\Domain\UseCase\Resource\ResourceTopLevelPathQuery;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindCreateCommand;
use Repeka\Tests\Integration\Traits\FixtureHelpers;

class PkImportClassificationsIntegrationTest extends AbstractPkImportIntegrationTest {
    use FixtureHelpers;

    /** @var ResourceKind */
    private $classificationRk;
    private $nameMetadata;
    private $languageMetadata;
    private $languageDescriptionMetadata;
    private $languageIdMetadata;

    /** @before */
    public function init(): void {
        $this->nameMetadata = $this->createSimpleMetadata('nazwa', MetadataControl::TEXT(), 'dictionaries');
        $this->languageMetadata = $this->handleCommandBypassingFirewall(
            new MetadataCreateCommand(
                'language',
                ['PL' => 'jezyk', 'EN' => 'language'],
                [],
                [],
                MetadataControl::INTEGER,
                'dictionaries',
                [],
                '',
                false,
                false,
                $this->nameMetadata
            )
        );
        $this->languageDescriptionMetadata = $this->handleCommandBypassingFirewall(
            new MetadataCreateCommand(
                'language_description',
                ['PL' => 'opis jezyka', 'EN' => 'language description'],
                [],
                [],
                MetadataControl::INTEGER,
                'dictionaries',
                [],
                '',
                false,
                false,
                $this->nameMetadata
            )
        );
        $this->languageIdMetadata = $this->handleCommandBypassingFirewall(
            new MetadataCreateCommand(
                'language_id',
                ['PL' => 'id jezyka', 'EN' => 'language id'],
                [],
                [],
                MetadataControl::INTEGER,
                'dictionaries',
                [],
                '',
                false,
                false,
                $this->languageMetadata
            )
        );
        $oldIdMetadata = $this->createSimpleMetadata('old_id', MetadataControl::INTEGER(), 'dictionaries');
        $this->classificationRk = $this->handleCommandBypassingFirewall(
            new ResourceKindCreateCommand(
                ['PL' => 'Klasyfikacja', 'EN' => 'Classification'],
                [$this->nameMetadata, $oldIdMetadata, $this->languageMetadata]
            )
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
        $this->mapRelations();
        $historiaFilozofii = $this->findResourceByContents(['nazwa' => 'Historia filozofii']);
        $pathToTop = $this->handleCommandBypassingFirewall(new ResourceTopLevelPathQuery($historiaFilozofii, SystemMetadata::PARENT));
        $this->assertTrue($historiaFilozofii->hasParent());
        $this->assertCount(1, $pathToTop);
        $nameMetadata = $this->findMetadataByName('nazwa', 'dictionaries');
        $this->assertEquals(['Filozofia'], $pathToTop[0]->getValues($nameMetadata));
    }

    public function testMapsMultipleRelationships() {
        $this->import();
        $this->mapRelations();
        $historiaReligioznawstwa = $this->findResourceByContents(['nazwa' => 'Historia i stan współczesny religioznawstwa']);
        $pathToTop = $this->handleCommandBypassingFirewall(new ResourceTopLevelPathQuery($historiaReligioznawstwa, SystemMetadata::PARENT));
        $this->assertTrue($historiaReligioznawstwa->hasParent());
        $this->assertCount(2, $pathToTop);
        $nameMetadata = $this->findMetadataByName('nazwa', 'dictionaries');
        $this->assertEquals(['Metareligioznawstwo (nauka o religioznawstwie)'], $pathToTop[0]->getValues($nameMetadata));
        $this->assertEquals(['Religioznawstwo. Religie'], $pathToTop[1]->getValues($nameMetadata));
    }

    public function testImportSubmetadata() {
        $this->import();
        $classificationByName = $this->findResourceByContents(['nazwa' => 'brak']);
        $metadataValues = $classificationByName->getValues($this->nameMetadata);
        $this->assertCount(2, $metadataValues);
        $firstMetadata = $metadataValues[0];
        $firstMetadataLangSubmetadata = $firstMetadata->getSubmetadata($this->languageMetadata);
        $this->assertCount(1, $firstMetadataLangSubmetadata);
        $this->assertEquals(5, $firstMetadataLangSubmetadata[0]->getValue());
        $firstMetadataLangIdSubSubmetadata = $firstMetadataLangSubmetadata[0]->getSubmetadata($this->languageIdMetadata);
        $this->assertCount(1, $firstMetadataLangIdSubSubmetadata);
        $this->assertEquals(1463, $firstMetadataLangIdSubSubmetadata[0]->getValue());
        $firstMetadataLangDescSubmetadata = $firstMetadata->getSubmetadata($this->languageDescriptionMetadata);
        $this->assertCount(1, $firstMetadataLangDescSubmetadata);
        $this->assertEquals(202, $firstMetadataLangDescSubmetadata[0]->getValue());
        $secondMetadata = $metadataValues[1];
        $secondMetadataLangSubmetadata = $secondMetadata->getSubmetadata($this->languageMetadata);
        $this->assertCount(1, $secondMetadataLangSubmetadata);
        $this->assertEquals(6, $secondMetadataLangSubmetadata[0]->getValue());
        $secMetadataLangIdSubSubmetadata = $secondMetadataLangSubmetadata[0]->getSubmetadata($this->languageIdMetadata);
        $this->assertCount(1, $secMetadataLangIdSubSubmetadata);
        $this->assertEquals(1464, $secMetadataLangIdSubSubmetadata[0]->getValue());
        $secondMetadataLangDescSubmetadata = $secondMetadata->getSubmetadata($this->languageDescriptionMetadata);
        $this->assertCount(1, $secondMetadataLangDescSubmetadata);
        $this->assertEquals(202, $secondMetadataLangDescSubmetadata[0]->getValue());
    }

    private function import() {
        $this->importFile('index-klasyfikacja_pkt', $this->classificationRk);
    }
}
