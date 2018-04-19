<?php
namespace Repeka\Tests\Integration\Repository;

use Repeka\Domain\Repository\AuditEntryRepository;
use Repeka\Domain\UseCase\Audit\AuditEntryListQuery;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;

class AuditEntryRepositoryIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;

    /** @var AuditEntryRepository */
    private $auditEntryRepository;

    public function setUp() {
        parent::setUp();
        $this->auditEntryRepository = $this->container->get(AuditEntryRepository::class);
        $this->loadAllFixtures();
    }

    public function testGetAuditedCommandNames() {
        $commandNames = $this->auditEntryRepository->getAuditedCommandNames();
        $this->assertTrue(count($commandNames) < 10);
        $this->assertContains('resource_create', $commandNames);
    }

    public function testFindByCommandName() {
        $query = AuditEntryListQuery::builder()->filterByCommandNames(['resource_create'])->build();
        $entries = $this->auditEntryRepository->findByQuery($query);
        $this->assertCount(16, $entries);
    }

    public function testPaginate() {
        $query = AuditEntryListQuery::builder()->setPage(1)->setResultsPerPage(5)->build();
        $entries = $this->auditEntryRepository->findByQuery($query);
        $this->assertCount(5, $entries);
        $this->assertGreaterThan(5, $entries->getTotalCount());
    }

    public function testFindByResourceContents() {
        $titleMetadata = $this->findMetadataByName('Tytuł');
        $query = AuditEntryListQuery::builder()->filterByResourceContents([$titleMetadata->getId() => 'PHP'])->build();
        $entries = $this->auditEntryRepository->findByQuery($query);
        $this->assertCount(4, $entries);
    }
}
