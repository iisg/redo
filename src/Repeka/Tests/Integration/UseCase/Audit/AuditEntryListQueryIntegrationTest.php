<?php
namespace Repeka\Tests\Integration\UseCase\MetadataImport;

use Repeka\Domain\UseCase\Audit\AuditEntryListQuery;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;

class AuditEntryListQueryIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;

    /** @before */
    public function loadFixtures() {
        $this->loadAllFixtures();
    }

    public function testFilterByMetadataIds() {
        $titleMetadata = $this->findMetadataByName('Tytuł');
        $query = AuditEntryListQuery::builder()->filterByResourceContents([$titleMetadata->getId() => 'PHP'])->build();
        $entries = $this->handleCommand($query);
        $this->assertCount(4, $entries);
    }

    public function testFilterByMetadataNames() {
        $query = AuditEntryListQuery::builder()->filterByResourceContents(['Tytuł' => 'PHP'])->build();
        $entries = $this->handleCommand($query);
        $this->assertCount(4, $entries);
    }
}
