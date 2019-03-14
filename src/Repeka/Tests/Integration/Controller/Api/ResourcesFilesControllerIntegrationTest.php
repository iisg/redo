<?php
namespace Repeka\Tests\Integration\Controller\Api;

use Repeka\Application\Controller\Api\ResourcesFilesController;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;

/** @small */
class ResourcesFilesControllerIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;

    /** @var ResourceEntity */
    private $phpBook;

    protected function initializeDatabaseForTests() {
        $this->loadAllFixtures();
        $this->phpBook = $this->getPhpBookResource();
        ResourcesFilesController::$fileManagerConnectorClassName = TestFileManagerConnector::class;
    }

    public function testCanOpenFileManagerIfAdmin() {
        $client = self::createAdminClient();
        $client->apiRequest('GET', "/api/resources/{$this->phpBook->getId()}/file-manager");
        $this->assertStatusCode(200, $client->getResponse());
    }

    public function testCanOpenFileManagerAsGodIfAdmin() {
        $client = self::createAdminClient();
        $client->apiRequest('GET', "/api/resources/{$this->phpBook->getId()}/file-manager?god=1");
        $this->assertStatusCode(200, $client->getResponse());
    }

    public function testCanOpenFileManagerIfOperator() {
        $client = self::createAuthenticatedClient('budynek', 'budynek');
        $client->apiRequest('GET', "/api/resources/{$this->phpBook->getId()}/file-manager");
        $this->assertStatusCode(200, $client->getResponse());
    }

    public function testCannotOpenFileManagerAsGodIfOperator() {
        $client = self::createAuthenticatedClient('budynek', 'budynek');
        $client->apiRequest('GET', "/api/resources/{$this->phpBook->getId()}/file-manager?god=1");
        $this->assertStatusCode(403, $client->getResponse());
    }
}
