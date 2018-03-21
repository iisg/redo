<?php
namespace Repeka\Tests\Integration\Repository;

use Repeka\Domain\Repository\AuditEntryRepository;
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
}
