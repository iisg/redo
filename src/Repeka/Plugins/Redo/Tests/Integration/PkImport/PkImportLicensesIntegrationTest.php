<?php
namespace Repeka\Plugins\Redo\Tests\Integration\PkImport;

use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindCreateCommand;
use Repeka\Tests\Integration\Traits\FixtureHelpers;

/** @small */
class PkImportLicensesIntegrationTest extends AbstractPkImportIntegrationTest {
    use FixtureHelpers;

    /** @var ResourceKind */
    private $licenseRk;

    public function initializeDatabaseForTests(): void {
        $nameMetadata = $this->createSimpleMetadata('nazwa', MetadataControl::TEXT(), 'dictionaries');
        $unifiedNameMetadata = $this->createSimpleMetadata('unified_name', MetadataControl::TEXT(), 'dictionaries');
        $oldIdMetadata = $this->createSimpleMetadata('old_id', MetadataControl::INTEGER(), 'dictionaries');
        $this->licenseRk = $this->handleCommandBypassingFirewall(
            new ResourceKindCreateCommand(['PL' => 'Licencja', 'EN' => 'License'], [$nameMetadata, $unifiedNameMetadata, $oldIdMetadata])
        );
    }

    public function testImportsLanguages() {
        $this->assertEquals(0, $this->getResourceRepository()->count(['kind' => $this->licenseRk]));
        $this->import();
        $this->assertEquals(17, $this->getResourceRepository()->count(['kind' => $this->licenseRk]));
    }

    public function testImportsUnifiedName() {
        $this->import();
        $licenseByName = $this->findResourceByContents(['nazwa' => '3.0 pl \(Uznanie autorstwa-Bez utworów zależnych']);
        $licenseByUnifiedName = $this->findResourceByContents(['unified_name' => 'cc_by_nd_30_pl']);
        $this->assertNotNull($licenseByName);
        $this->assertEquals($licenseByName, $licenseByUnifiedName);
    }

    public function testImportsOldId() {
        $this->import();
        $licenseByName = $this->findResourceByContents(['unified_name' => 'cc_by_nc_sa_30_pl']);
        $licenseByOldId = $this->findResourceByContents(['old_id' => 1651]);
        $this->assertNotNull($licenseByName);
        $this->assertEquals($licenseByName, $licenseByOldId);
    }

    private function import() {
        $this->importFile('index-licencje', $this->licenseRk);
    }
}
