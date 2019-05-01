<?php
namespace Repeka\Tests\Integration\UseCase\MetadataImport;

use Repeka\Domain\UseCase\Audit\AuditEntryListQuery;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;

/** @small */
class AuditEntryListQueryIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;

    private const MOMENT_DATE_FORMAT = 'Y-m-d\TH:i:s';
    private const NUMBER_OF_FIXTURE_AUDIT_ENTRIES = 34;

    protected function initializeDatabaseForTests() {
        $this->loadAllFixtures();
    }

    public function testFilterByDateFrom() {
        $today = date(self::MOMENT_DATE_FORMAT);
        $dateFrom = date(self::MOMENT_DATE_FORMAT, strtotime('-1 month', strtotime($today)));
        $query = AuditEntryListQuery::builder()->filterByDateFrom($dateFrom)->build();
        $entries = $this->handleCommandBypassingFirewall($query);
        $this->assertCount(self::NUMBER_OF_FIXTURE_AUDIT_ENTRIES, $entries);
    }

    public function testFilterByDateTo() {
        $today = date(self::MOMENT_DATE_FORMAT);
        $dateTo = date(self::MOMENT_DATE_FORMAT, strtotime('+1 month', strtotime($today)));
        $query = AuditEntryListQuery::builder()->filterByDateTo($dateTo)->build();
        $entries = $this->handleCommandBypassingFirewall($query);
        $this->assertCount(self::NUMBER_OF_FIXTURE_AUDIT_ENTRIES, $entries);
    }

    public function testFilterByDateFromTo() {
        $today = date(self::MOMENT_DATE_FORMAT);
        $dateFrom = date(self::MOMENT_DATE_FORMAT, strtotime('-2 week', strtotime($today)));
        $dateTo = date(self::MOMENT_DATE_FORMAT, strtotime('+2 week', strtotime($today)));
        $query = AuditEntryListQuery::builder()->filterByDateFrom($dateFrom)->filterByDateTo($dateTo)->build();
        $entries = $this->handleCommandBypassingFirewall($query);
        $this->assertCount(self::NUMBER_OF_FIXTURE_AUDIT_ENTRIES, $entries);
    }

    public function testFilterByFutureDateFrom() {
        $today = date(self::MOMENT_DATE_FORMAT);
        $dateFrom = date(self::MOMENT_DATE_FORMAT, strtotime('+1 week', strtotime($today)));
        $query = AuditEntryListQuery::builder()->filterByDateFrom($dateFrom)->build();
        $entries = $this->handleCommandBypassingFirewall($query);
        $this->assertCount(0, $entries);
    }

    public function testFilterByPastDateTo() {
        $today = date(self::MOMENT_DATE_FORMAT);
        $dateTo = date(self::MOMENT_DATE_FORMAT, strtotime('-1 week', strtotime($today)));
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
