<?php
namespace Repeka\Tests\Integration;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowTransition;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Workflow\ResourceWorkflowDriver;
use Repeka\Tests\Domain\Factory\SampleResourceWorkflowDriverFactory;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;

class TaskIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;
    const ENDPOINT = '/api/tasks';

    /** @var Metadata */
    private $parentMetadata;
    /** @var Metadata */
    private $metadata1;
    /** @var Metadata */
    private $metadata2;
    /** @var ResourceWorkflowPlace */
    private $workflowPlace1;
    /** @var ResourceWorkflowTransition */
    private $transition;
    /** @var ResourceKind */
    private $resourceKind;
    /** @var ResourceEntity */
    private $resource;

    protected function setUp() {
        parent::setUp();
        $this->clearDefaultLanguages();
        $this->createLanguage('PL', 'PL', 'polski'); //for validate parentMetadata
        $this->createLanguage('EN', 'EN', 'angielski'); //for validate parentMetadata
        /** @var MetadataRepository $metadataRepository */
        $metadataRepository = $this->container->get(MetadataRepository::class);
        $this->parentMetadata = $metadataRepository->findOne(SystemMetadata::PARENT);
        $this->metadata1 = $this->createMetadata('M1', ['PL' => 'metadata', 'EN' => 'metadata'], [], [], 'textarea');
        $this->metadata2 = $this->createMetadata(
            'M2',
            ['PL' => 'metadata', 'EN' => 'metadata'],
            [],
            [],
            'relationship',
            'books',
            ['resourceKind' => [-1]]
        );
        $this->workflowPlace1 = new ResourceWorkflowPlace(['PL' => 'key1', 'EN' => 'key1'], 'p1', [], [], [], []);
        $workflowPlace2 = new ResourceWorkflowPlace(['PL' => 'key2', 'EN' => 'key2'], 'p2', [], [], [$this->metadata2->getId()], []);
        $this->transition = new ResourceWorkflowTransition(['PL' => 'key3', 'EN' => 'key3'], ['p1'], ['p2'], 't1');
        $workflow = $this->createWorkflow(
            ['PL' => 'Workflow', 'EN' => 'Workflow'],
            'books',
            [$this->workflowPlace1, $workflowPlace2],
            [$this->transition]
        );
        $sampleResourceWorkflowDriverFactory = new SampleResourceWorkflowDriverFactory($this->createMock(ResourceWorkflowDriver::class));
        $workflow = $sampleResourceWorkflowDriverFactory->setForWorkflow($workflow);
        $this->resourceKind = $this->createResourceKind(
            'Resource kind',
            ['PL' => 'Resource kind', 'EN' => 'Resource kind'],
            [$this->parentMetadata, $this->metadata1, $this->metadata2],
            $workflow
        );
        $this->resource = $this->createResource(
            $this->resourceKind,
            [
                $this->metadata1->getId() => ['Test value'],
                $this->metadata2->getId() => [$this->getAdminUser()->getUserData()->getId()],
            ]
        );
    }

    public function testFetchingTasksReturnsOnlyMyTasks() {
        $client = self::createAdminClient();
        $client->apiRequest('GET', self::ENDPOINT);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $responseContent = json_decode($response->getContent(), true);
        $this->assertCount(1, $responseContent);
        $this->assertArrayHasKey('myTasks', $responseContent[0]);
        $this->assertArrayHasKey('possibleTasks', $responseContent[0]);
        $this->assertEquals('books', $responseContent[0]['resourceClass']);
        $this->assertCount(1, $responseContent[0]['myTasks']);
        $this->assertCount(0, $responseContent[0]['possibleTasks']);
        $this->assertEquals($this->resource->getId(), $responseContent[0]['myTasks'][0]['id']);
    }

    public function testFetchingTasksReturnsOnlyPossibleTasks() {
        $groupResource = $this->createResource(
            $this->resourceKind,
            [
                $this->metadata1->getId() => ['Test value 2'],
                $this->metadata2->getId() => [$this->getAdminUser()->getUserData()->getId(), 5],
            ]
        );
        $client = self::createAdminClient();
        $client->apiRequest('GET', self::ENDPOINT);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $responseContent = json_decode($response->getContent(), true);
        $this->assertCount(1, $responseContent);
        $this->assertArrayHasKey('myTasks', $responseContent[0]);
        $this->assertArrayHasKey('possibleTasks', $responseContent[0]);
        $this->assertEquals('books', $responseContent[0]['resourceClass']);
        $this->assertCount(1, $responseContent[0]['myTasks']);
        $this->assertCount(1, $responseContent[0]['possibleTasks']);
        $this->assertEquals($this->resource->getId(), $responseContent[0]['myTasks'][0]['id']);
        $this->assertEquals($groupResource->getId(), $responseContent[0]['possibleTasks'][0]['id']);
    }
}
