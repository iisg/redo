<?php
namespace Repeka\Plugins\Redo\Tests\Integration\PkImport;

use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindCreateCommand;
use Repeka\Tests\Integration\Traits\FixtureHelpers;

class PkImportLanguagesIntegrationTest extends AbstractPkImportIntegrationTest {
    use FixtureHelpers;

    /** @var ResourceKind */
    private $languageRk;

    /** @before */
    public function init(): void {
        $nameMetadata = $this->createSimpleMetadata('nazwa', MetadataControl::TEXT(), 'dictionaries');
        $isoMetadata = $this->createSimpleMetadata('iso_code', MetadataControl::TEXT(), 'dictionaries');
        $oldIdMetadata = $this->createSimpleMetadata('old_id', MetadataControl::INTEGER(), 'dictionaries');
        $this->languageRk = $this->handleCommandBypassingFirewall(
            new ResourceKindCreateCommand(['PL' => 'Język', 'EN' => 'Language'], [$nameMetadata, $isoMetadata, $oldIdMetadata])
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
