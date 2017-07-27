<?php
namespace Repeka\Tests\Integration;

use Repeka\Tests\IntegrationTestCase;

class WorkflowIntegrationTest extends IntegrationTestCase {
    const ENDPOINT = '/api/workflows';

    public function setUp() {
        parent::setUp();
    }

    public function testFetchingWorkflows() {
        $client = self::createAdminClient();
        $client->apiRequest('GET', self::ENDPOINT, [], ['resourceClass' => 'books']);
        $this->assertStatusCode(200, $client->getResponse());
    }

    public function testFetchingWorkflowsFailsWhenInvalidResourceClass() {
        $client = self::createAdminClient();
        $client->apiRequest('GET', self::ENDPOINT, [], ['resourceClass' => 'resourceClass']);
        $this->assertStatusCode(400, $client->getResponse());
    }
}
