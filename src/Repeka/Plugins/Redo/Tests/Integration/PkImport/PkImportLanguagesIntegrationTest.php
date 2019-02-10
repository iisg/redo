<?php
namespace Repeka\Plugins\Redo\Tests\Integration\PkImport;

use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\MetadataValue;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindCreateCommand;
use Repeka\Tests\Integration\Traits\FixtureHelpers;

/** @small */
class PkImportLanguagesIntegrationTest extends AbstractPkImportIntegrationTest {
    use FixtureHelpers;

    /** @var ResourceKind */
    private $languageRk;

    public function initializeDatabaseForTests(): void {
        $nameMetadata = $this->createSimpleMetadata('nazwa', MetadataControl::TEXT(), 'dictionaries');
        $this->createMetadata(
            'jezykNazwy',
            ['PL' => 'jezykNazwy', 'EN' => 'jezykNazwy'],
            [],
            [],
            MetadataControl::SYSTEM_LANGUAGE,
            'dictionaries',
            [],
            '',
            $nameMetadata
        );
        $isoMetadata = $this->createSimpleMetadata('iso_code', MetadataControl::TEXT(), 'dictionaries');
        $oldIdMetadata = $this->createSimpleMetadata('old_id', MetadataControl::INTEGER(), 'dictionaries');
        $this->languageRk = $this->handleCommandBypassingFirewall(
            new ResourceKindCreateCommand('language', ['PL' => 'Język', 'EN' => 'Language'], [$nameMetadata, $isoMetadata, $oldIdMetadata])
        );
    }

    public function testImportsLanguages() {
        $this->assertEquals(0, $this->getResourceRepository()->count(['kind' => $this->languageRk]));
        $this->import();
        $this->assertEquals(25, $this->getResourceRepository()->count(['kind' => $this->languageRk]));
    }

    public function testImportsLabelsInBothLanguages() {
        $this->import();
        $polishLanguage = $this->findResourceByContents(['nazwa' => 'polish']);
        $polskiLanguage = $this->findResourceByContents(['nazwa' => 'polski']);
        $this->assertNotNull($polishLanguage);
        $this->assertEquals($polishLanguage, $polskiLanguage);
    }

    public function testImportsLabelsInBothLanguagesAndAssignsCorrectLanguage() {
        $this->import();
        $nameMetadata = $this->findMetadataByName('nazwa');
        $nameLanguageMetadata = $this->findMetadataByName('jezykNazwy');
        $polishLanguage = $this->findResourceByContents(['nazwa' => 'polish']);
        $names = $polishLanguage->getValues($nameMetadata);
        $this->assertCount(2, $names);
        $this->assertEquals([new MetadataValue('PL')], $names[0]->getSubmetadata($nameLanguageMetadata));
        $this->assertEquals([new MetadataValue('EN')], $names[1]->getSubmetadata($nameLanguageMetadata));
    }

    public function testImportsIsoCode() {
        $this->import();
        $polishLanguage = $this->findResourceByContents(['nazwa' => 'polish']);
        $polLanguage = $this->findResourceByContents(['iso_code' => 'pol']);
        $this->assertNotNull($polishLanguage);
        $this->assertEquals($polishLanguage, $polLanguage);
    }

    public function testImportsOldId() {
        $this->import();
        $languageByName = $this->findResourceByContents(['nazwa' => 'litewski']);
        $languageByOldId = $this->findResourceByContents(['old_id' => 1645]);
        $this->assertNotNull($languageByName);
        $this->assertEquals($languageByName, $languageByOldId);
    }

    private function import() {
        $this->importFile('index-jezyki', $this->languageRk);
    }
}
