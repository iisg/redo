<?php
namespace Repeka\Tests\Integration;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowTransition;
use Repeka\Domain\Repository\AuditEntryRepository;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Workflow\ResourceWorkflowDriver;
use Repeka\Tests\Domain\Factory\SampleResourceWorkflowDriverFactory;
use Repeka\Tests\IntegrationTestCase;

class ResourceIntegrationTest extends IntegrationTestCase {
    const ENDPOINT = '/api/resources';

    /** @var Metadata */
    private $metadata1;
    /** @var Metadata */
    private $metadata2;
    /** @var Metadata */
    private $metadata3;
    /** @var Metadata */
    private $metadata4;
    /** @var ResourceKind */
    private $resourceKind;
    /** @var ResourceKind */
    private $resourceKindWithWorkflow;
    /** @var  Metadata */
    private $parentMetadata;
    /** @var ResourceEntity */
    private $resource;
    /** @var ResourceEntity */
    private $resourceWithWorkflow;
    /** @var ResourceEntity */
    private $parentResource;
    /** @var ResourceEntity */
    private $childResource;
    /** @var  ResourceWorkflowPlace */
    private $workflowPlace1;
    /** @var ResourceWorkflowTransition */
    private $transition;

    protected function setUp() {
        parent::setUp();
        $this->clearDefaultLanguages();
        $this->createLanguage('TEST', 'te_ST', 'Test language');
        /** @var MetadataRepository $metadataRepository */
        $metadataRepository = $this->container->get(MetadataRepository::class);
        $this->parentMetadata = $metadataRepository->findOne(SystemMetadata::PARENT);
        $this->metadata1 = $this->createMetadata('M1', ['TEST' => 'metadata'], [], [], 'textarea');
        $this->metadata2 = $this->createMetadata('M2', ['TEST' => 'metadata'], [], [], 'textarea');
        $this->metadata3 = $this->createMetadata('M3', ['TEST' => 'metadata'], [], [], 'relationship', 'books', ['resourceKind' => [-1]]);
        $this->metadata4 = $this->createMetadata('M4', ['TEST' => 'metadata'], [], [], 'relationship', 'books', ['resourceKind' => [-1]]);
        $this->workflowPlace1 = new ResourceWorkflowPlace(['key1'], 'p1', [], [], [], [$this->metadata3->getId()]);
        $workflowPlace2 = new ResourceWorkflowPlace(['key2'], 'p2', [], [], [], [$this->metadata4->getId()]);
        $this->transition = new ResourceWorkflowTransition(['key3'], ['p1'], ['p2'], [-1, 1], 't1');
        $workflow = $this->createWorkflow(['TEST' => 'Workflow'], 'books', [$this->workflowPlace1, $workflowPlace2], [$this->transition]);
        $sampleResourceWorkflowDriverFactory = new SampleResourceWorkflowDriverFactory($this->createMock(ResourceWorkflowDriver::class));
        $workflow = $sampleResourceWorkflowDriverFactory->setForWorkflow($workflow);
        $this->resourceKind = $this->createResourceKind(
            ['TEST' => 'Resource kind'],
            [$this->metadata1, $this->metadata2]
        );
        $this->resourceKindWithWorkflow = $this->createResourceKind(
            ['TEST' => 'Resource kind'],
            [$this->parentMetadata, $this->metadata1, $this->metadata3, $this->metadata4],
            [],
            $workflow
        );
        $this->resource = $this->createResource(
            $this->resourceKind,
            [
                $this->metadata1->getId() => ['Test value'],
            ]
        );
        $this->parentResource = $this->createResource(
            $this->resourceKind,
            [
                $this->metadata1->getId() => ['Test value for parent'],
            ]
        );
        $this->resourceWithWorkflow = $this->createResource(
            $this->resourceKindWithWorkflow,
            [
                $this->metadata1->getId() => ['Test value'],
            ]
        );
        $this->childResource = $this->createResource(
            $this->resourceKind,
            [
                -1 => [$this->parentResource->getId()],
                $this->metadata1->getId() => ['Test value for child'],
            ]
        );
    }

    public function testFetchingResources() {
        $client = self::createAdminClient();
        $client->apiRequest('GET', self::ENDPOINT, [], ['resourceClasses' => ['books']]);
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertJsonStringSimilarToArray(
            [
                [
                    'id' => $this->resource->getId(),
                    'kindId' => $this->resourceKind->getId(),
                    'contents' => [$this->metadata1->getId() => [['value' => 'Test value']]],
                    'resourceClass' => $this->resource->getResourceClass(),
                    'displayStrategies' => [],
                ],
                [
                    'id' => $this->parentResource->getId(),
                    'kindId' => $this->resourceKind->getId(),
                    'contents' => [$this->metadata1->getId() => [['value' => 'Test value for parent']]],
                    'resourceClass' => $this->resource->getResourceClass(),
                    'displayStrategies' => [],
                ],
                [
                    'id' => $this->childResource->getId(),
                    'kindId' => $this->resourceKind->getId(),
                    'contents' => [
                        $this->metadata1->getId() => [['value' => 'Test value for child']],
                        SystemMetadata::PARENT => [['value' => $this->parentResource->getId()]],
                    ],
                    'resourceClass' => $this->resource->getResourceClass(),
                    'displayStrategies' => [],
                ],
                [
                    'id' => $this->resourceWithWorkflow->getId(),
                    'kindId' => $this->resourceKindWithWorkflow->getId(),
                    'contents' => [$this->metadata1->getId() => [['value' => 'Test value']]],
                    'resourceClass' => $this->resource->getResourceClass(),
                    'currentPlaces' => [$this->workflowPlace1->toArray()],
                    'availableTransitions' => [$this->transition->toArray()],
                    'blockedTransitions' => [],
                    'transitionAssigneeMetadata' => [],
                    'displayStrategies' => [],
                ],
            ],
            $client->getResponse()->getContent()
        );
        $this->assertEquals(4, $client->getResponse()->headers->get('pk_total'));
    }

    public function testFetchingResourcesForFirstPageWithTwoByPage() {
        $client = self::createAdminClient();
        $client->apiRequest('GET', self::ENDPOINT, [], ['resourceClasses' => ['books'], 'page' => 1, 'resultsPerPage' => 2]);
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertJsonStringSimilarToArray(
            [
                [
                    'id' => $this->resource->getId(),
                    'kindId' => $this->resourceKind->getId(),
                    'contents' => ResourceContents::fromArray([$this->metadata1->getId() => ['Test value']])->toArray(),
                    'resourceClass' => $this->resource->getResourceClass(),
                    'displayStrategies' => [],
                ],
                [
                    'id' => $this->parentResource->getId(),
                    'kindId' => $this->resourceKind->getId(),
                    'contents' => ResourceContents::fromArray([$this->metadata1->getId() => ['Test value for parent']])->toArray(),
                    'resourceClass' => $this->resource->getResourceClass(),
                    'displayStrategies' => [],
                ],
            ],
            $client->getResponse()->getContent()
        );
        $this->assertEquals(4, $client->getResponse()->headers->get('pk_total'));
    }

    public function testFetchingTopLevelResources() {
        $client = self::createAdminClient();
        $client->apiRequest('GET', self::ENDPOINT, [], ['resourceClasses' => ['books'], 'topLevel' => true]);
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertJsonStringSimilarToArray(
            [
                [
                    'id' => $this->resource->getId(),
                    'kindId' => $this->resourceKind->getId(),
                    'contents' => ResourceContents::fromArray([$this->metadata1->getId() => ['Test value']])->toArray(),
                    'resourceClass' => $this->resource->getResourceClass(),
                    'displayStrategies' => [],
                ],
                [
                    'id' => $this->parentResource->getId(),
                    'kindId' => $this->resourceKind->getId(),
                    'contents' => ResourceContents::fromArray([$this->metadata1->getId() => ['Test value for parent']])->toArray(),
                    'resourceClass' => $this->resource->getResourceClass(),
                    'displayStrategies' => [],
                ],
                [
                    'id' => $this->resourceWithWorkflow->getId(),
                    'kindId' => $this->resourceKindWithWorkflow->getId(),
                    'contents' => [$this->metadata1->getId() => [['value' => 'Test value']]],
                    'resourceClass' => $this->resource->getResourceClass(),
                    'currentPlaces' => [$this->workflowPlace1->toArray()],
                    'availableTransitions' => [$this->transition->toArray()],
                    'blockedTransitions' => [],
                    'transitionAssigneeMetadata' => [],
                    'displayStrategies' => [],
                ],
            ],
            $client->getResponse()->getContent()
        );
        $this->assertEquals(3, $client->getResponse()->headers->get('pk_total'));
    }

    public function testFetchingResourcesFailsWhenInvalidResourceClass() {
        $client = self::createAdminClient();
        $client->apiRequest('GET', self::ENDPOINT, [], ['resourceClasses' => ['resourceClass']]);
        $this->assertStatusCode(400, $client->getResponse());
    }

    public function testFetchingSingleResource() {
        $client = self::createAdminClient();
        $client->apiRequest('GET', self::oneEntityEndpoint($this->resource->getId()));
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertJsonStringSimilarToArray(
            [
                'id' => $this->resource->getId(),
                'kindId' => $this->resourceKind->getId(),
                'contents' => ResourceContents::fromArray([$this->metadata1->getId() => ['Test value']])->toArray(),
                'resourceClass' => $this->resource->getResourceClass(),
                'displayStrategies' => [],
            ],
            $client->getResponse()->getContent()
        );
    }

    public function testFetchingByParentId() {
        $client = self::createAdminClient();
        $client->apiRequest('GET', self::ENDPOINT, [], ['parentId' => $this->parentResource->getId()]);
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertJsonStringSimilarToArray(
            [
                [
                    'id' => $this->childResource->getId(),
                    'kindId' => $this->resourceKind->getId(),
                    'contents' => [
                        $this->metadata1->getId() => [['value' => 'Test value for child']],
                        SystemMetadata::PARENT => [['value' => $this->parentResource->getId()]],
                    ],
                    'resourceClass' => $this->resource->getResourceClass(),
                    'displayStrategies' => [],
                ],
            ],
            $client->getResponse()->getContent()
        );
    }

    public function testFetchingSortedResources() {
        $client = self::createAdminClient();
        $client->apiRequest(
            'GET',
            self::ENDPOINT,
            [],
            [
                'resourceClasses' => ['books'],
                'topLevel' => true,
                'sortByIds' => [0 => ['metadataId' => $this->metadata1->getId(), 'direction' => 'DESC']],
            ]
        );
        $this->assertStatusCode(200, $client->getResponse());
        $expectedOrder = [$this->parentResource->getId(), $this->resource->getId()];
        $fetchedResource = json_decode($client->getResponse()->getContent());
        $actualOrder[] = $fetchedResource[0]->id;
        $actualOrder[] = $fetchedResource[1]->id;
        $this->assertEquals($expectedOrder, $actualOrder);
        $this->assertEquals(3, $client->getResponse()->headers->get('pk_total'));
    }

    public function testCreatingResource(): int {
        $client = self::createAdminClient();
        $client->apiRequest(
            'POST',
            self::ENDPOINT,
            [
                'kindId' => $this->resourceKind->getId(),
                'contents' => json_encode([$this->metadata1->getId() => ['created']]),
                'resourceClass' => 'books',
            ]
        );
        $this->assertStatusCode(201, $client->getResponse());
        $repository = self::createClient()->getContainer()->get(ResourceRepository::class);
        $createdId = json_decode($client->getResponse()->getContent())->id;
        /** @var ResourceEntity $created */
        $created = $repository->findOne($createdId);
        $this->assertEquals($this->resourceKind->getId(), $created->getKind()->getId());
        $this->assertEquals(ResourceContents::fromArray([$this->metadata1->getId() => ['created']]), $created->getContents());
        return $createdId;
    }

    public function testCreatingResourceSavesAuditEntry() {
        $createdId = $this->testCreatingResource();
        $auditEntries = $this->container->get(AuditEntryRepository::class)->findAll();
        $latestAuditEntry = end($auditEntries);
        $this->assertEquals('resource_create', $latestAuditEntry->getCommandName());
        $this->assertEquals($createdId, $latestAuditEntry->getData()['resource']['id']);
    }

    public function testCreatingResourceWithWorkflow() {
        $client = self::createAdminClient();
        $client->apiRequest(
            'POST',
            self::ENDPOINT,
            [
                'kindId' => $this->resourceKindWithWorkflow->getId(),
                'contents' => json_encode([$this->metadata1->getId() => ['created']]),
                'resourceClass' => 'books',
            ]
        );
        $this->assertStatusCode(201, $client->getResponse());
        $repository = self::createClient()->getContainer()->get(ResourceRepository::class);
        $createdId = json_decode($client->getResponse()->getContent())->id;
        /** @var ResourceEntity $created */
        $created = $repository->findOne($createdId);
        $this->assertEquals($this->resourceKindWithWorkflow->getId(), $created->getKind()->getId());
        $this->assertEquals(
            ResourceContents::fromArray([$this->metadata1->getId() => ['created'], $this->metadata3->getId() => 1]),
            $created->getContents()
        );
        $this->assertEquals(['p1' => true], $created->getMarking());
    }

    public function testEditingResource() {
        $client = self::createAdminClient();
        $client->apiRequest(
            'POST',
            self::oneEntityEndpoint($this->resource->getId()),
            [
                'id' => $this->resource->getId(),
                'kindId' => $this->resourceKind->getId(),
                'contents' => json_encode([$this->metadata1->getId() => ['edited']]),
            ]
        );
        $this->assertStatusCode(200, $client->getResponse());
        $repository = self::createClient()->getContainer()->get(ResourceRepository::class);
        /** @var ResourceEntity $edited */
        $edited = $repository->findOne($this->resource->getId());
        $this->assertEquals(ResourceContents::fromArray([$this->metadata1->getId() => ['edited']]), $edited->getContents());
    }

    public function testAutoAssignMetadataWhenEditingResourceWithWorkflow() {
        $client = self::createAdminClient();
        $endpoint = self::resourceEntityWithTransitionEndpoint($this->resourceWithWorkflow->getId());
        $client->apiRequest(
            'POST',
            $endpoint . '?' . http_build_query(['transitionId' => 't1']),
            [
                'id' => $this->resourceWithWorkflow->getId(),
                'kindId' => $this->resourceKindWithWorkflow->getId(),
                'contents' => json_encode([$this->metadata1->getId() => ['edited'], $this->metadata3->getId() => 1]),
            ]
        );
        $this->assertStatusCode(200, $client->getResponse());
        $repository = self::createClient()->getContainer()->get(ResourceRepository::class);
        /** @var ResourceEntity $edited */
        $edited = $repository->findOne($this->resourceWithWorkflow->getId());
        $this->assertEquals(
            ResourceContents::fromArray(
                [$this->metadata1->getId() => ['edited'], $this->metadata3->getId() => 1, $this->metadata4->getId() => 1]
            ),
            $edited->getContents()
        );
    }

    public function testEditingResourceKindFails() {
        $newResourceKind = $this->createResourceKind(
            ['TEST' => 'Replacement resource kind'],
            [$this->parentMetadata, $this->metadata1, $this->metadata2]
        );
        $newResourceKind->getMetadataList()[0]->updateOrdinalNumber(0);
        $this->persistAndFlush($newResourceKind->getMetadataList()[1]);
        $client = self::createAdminClient();
        $client->apiRequest(
            'POST',
            self::oneEntityEndpoint($this->resource->getId()),
            [
                'kindId' => $newResourceKind->getId(),
                'contents' => json_encode($this->resource->getContents()),
            ]
        );
        $this->assertStatusCode(200, $client->getResponse());
        $repository = self::createClient()->getContainer()->get(ResourceRepository::class);
        /** @var ResourceEntity $edited */
        $edited = $repository->findOne($this->resource->getId());
        $this->assertEquals($this->resourceKind->getId(), $edited->getKind()->getId());
    }

    public function testDeletingLeafResource() {
        $client = self::createAdminClient();
        $client->apiRequest('DELETE', self::oneEntityEndpoint($this->childResource->getId()));
        $this->assertStatusCode(204, $client->getResponse());
        /** @var ResourceRepository $repository */
        $repository = self::createClient()->getContainer()->get(ResourceRepository::class);
        $this->assertFalse($repository->exists($this->childResource->getId()));
    }

    public function testDeletingParentResourceIsForbidden() {
        $client = self::createAdminClient();
        $client->apiRequest('DELETE', self::oneEntityEndpoint($this->parentResource->getId()));
        $this->assertStatusCode(400, $client->getResponse());
        /** @var ResourceRepository $repository */
        $repository = self::createClient()->getContainer()->get(ResourceRepository::class);
        $this->assertTrue($repository->exists($this->parentResource->getId()));
    }
}
