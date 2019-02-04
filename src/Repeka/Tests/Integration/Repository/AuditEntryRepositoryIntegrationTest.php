<?php
namespace Repeka\Tests\Integration\Repository;

use Repeka\Domain\Repository\AuditEntryRepository;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Audit\AuditedCommandNamesQuery;
use Repeka\Domain\UseCase\Audit\AuditEntryListQuery;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;

/** @small */
class AuditEntryRepositoryIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;

    /** @var AuditEntryRepository */
    private $auditEntryRepository;
    /** @var ResourceRepository */
    private $resourceRepository;

    public function initializeDatabaseForTests() {
        $this->loadAllFixtures();
        $this->auditEntryRepository = $this->container->get(AuditEntryRepository::class);
        $this->resourceRepository = $this->container->get(ResourceRepository::class);
    }

    public function testGetAllAuditedCommandNames() {
        $commandNames = $this->auditEntryRepository->getAuditedCommandNames(new AuditedCommandNamesQuery(false));
        $this->assertTrue(count($commandNames) < 10);
        $this->assertContains('resource_create', $commandNames);
    }

    public function testGetResourceAuditedCommandNames() {
        $commandNames = $this->auditEntryRepository->getAuditedCommandNames(new AuditedCommandNamesQuery(true));
        $this->assertTrue(count($commandNames) < 10);
        $this->assertNotContains('user_authenticate', $commandNames);
        $this->assertContains('resource_create', $commandNames);
    }

    public function testFindByCommandName() {
        $query = AuditEntryListQuery::builder()->filterByCommandNames(['resource_create'])->build();
        $entries = $this->auditEntryRepository->findByQuery($query);
        $this->assertCount($this->resourceRepository->count([]), $entries);
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

    public function testFindByResourceId() {
        $resourceId = 12;
        $query = AuditEntryListQuery::builder()->filterByResourceId($resourceId)->build();
        $entries = $this->auditEntryRepository->findByQuery($query);
        $this->assertCount(3, $entries);
    }
}
