<?php
namespace Repeka\Tests\Integration;

use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
use Repeka\Domain\Repository\ResourceWorkflowRepository;
use Repeka\Tests\IntegrationTestCase;

class ResourceWorkflowIntegrationTest extends IntegrationTestCase {
    const ENDPOINT = '/api/workflows';

    /** @var ResourceWorkflow */
    private $workflow;

    protected function setUp() {
        parent::setUp();
        $this->clearDefaultLanguages();
        $this->createLanguage('TEST', 'te_ST', 'Test language');
        $this->workflow = $this->createWorkflow(['TEST' => 'Test workflow'], 'books', [new ResourceWorkflowPlace([], 'abc')], []);
    }

    public function testRenamingWorkflow() {
        $client = self::createAdminClient();
        $newName = ['TEST' => 'New test name'];
        $client->apiRequest('PUT', $this->oneEntityEndpoint($this->workflow), [
            'name' => $newName,
            'places' => [
                ['id' => "abc", 'label' => ['TEST' => 'place']],
            ],
            'resourceClass' => 'books',
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        /** @var ResourceWorkflowRepository $repository */
        $repository = self::createClient()->getContainer()->get(ResourceWorkflowRepository::class);
        $edited = $repository->findOne($this->workflow->getId());
        $this->assertEquals($newName, $edited->getName());
    }
}
