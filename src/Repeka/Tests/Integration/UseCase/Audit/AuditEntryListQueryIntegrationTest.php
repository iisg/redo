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

    public function testFilterByDateFrom() {
        $today = date('Y-m-d');
        $dateFrom = date('Y-m-d', strtotime('-1 month', strtotime($today)));
        $query = AuditEntryListQuery::builder()->filterByDateFrom($dateFrom)->build();
        $entries = $this->handleCommandBypassingFirewall($query);
        $this->assertCount(25, $entries);
    }

    public function testFilterByDateTo() {
        $today = date('Y-m-d');
        $dateTo = date('Y-m-d', strtotime('+1 month', strtotime($today)));
        $query = AuditEntryListQuery::builder()->filterByDateTo($dateTo)->build();
        $entries = $this->handleCommandBypassingFirewall($query);
        $this->assertCount(25, $entries);
    }

    public function testFilterByDateFromTo() {
        $today = date('Y-m-d');
        $dateFrom = date('Y-m-d', strtotime('-2 week', strtotime($today)));
        $dateTo = date('Y-m-d', strtotime('+2 week', strtotime($today)));
        $query = AuditEntryListQuery::builder()->filterByDateFrom($dateFrom)->filterByDateTo($dateTo)->build();
        $entries = $this->handleCommandBypassingFirewall($query);
        $this->assertCount(25, $entries);
    }

    public function testFilterByFutureDateFrom() {
        $today = date('Y-m-d');
        $dateFrom = date('Y-m-d', strtotime('+1 week', strtotime($today)));
        $query = AuditEntryListQuery::builder()->filterByDateFrom($dateFrom)->build();
        $entries = $this->handleCommandBypassingFirewall($query);
        $this->assertCount(0, $entries);
    }

    public function testFilterByPastDateTo() {
        $today = date('Y-m-d');
        $dateTo = date('Y-m-d', strtotime('-1 week', strtotime($today)));
        $query = AuditEntryListQuery::builder()->filterByDateTo($dateTo)->build();
        $entries = $this->handleCommandBypassingFirewall($query);
        $this->assertCount(0, $entries);
    }

    public function testFilterByMetadataIds() {
        $titleMetadata = $this->findMetadataByName('Tytuł');
        $query = AuditEntryListQuery::builder()->filterByResourceContents([$titleMetadata->getId() => 'PHP'])->build();
        $entries = $this->handleCommandBypassingFirewall($query);
        $this->assertCount(4, $entries);
    }

    public function testFilterByMetadataNames() {
        $query = AuditEntryListQuery::builder()->filterByResourceContents(['Tytuł' => 'PHP'])->build();
        $entries = $this->handleCommandBypassingFirewall($query);
        $this->assertCount(4, $entries);
    }
}
