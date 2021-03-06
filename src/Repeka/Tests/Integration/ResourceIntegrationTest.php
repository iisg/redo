<?php
namespace Repeka\Tests\Integration;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Constants\SystemResource;
use Repeka\Domain\Constants\SystemResourceKind;
use Repeka\Domain\Constants\SystemTransition;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowTransition;
use Repeka\Domain\Repository\AuditEntryRepository;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Resource\ResourceListFtsQuery;
use Repeka\Domain\Utils\EntityUtils;
use Repeka\Domain\Workflow\ResourceWorkflowDriver;
use Repeka\Tests\Domain\Factory\SampleResourceWorkflowDriverFactory;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class ResourceIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;
    const ENDPOINT = '/api/resources';

    /** @var Metadata */
    private $metadata1;
    /** @var Metadata */
    private $metadata2;
    /** @var Metadata */
    private $metadata3;
    /** @var Metadata */
    private $metadata4;
    /** @var Metadata */
    private $metadata5;
    /** @var Metadata */
    private $metadata6;
    /** @var ResourceKind */
    private $resourceKind;
    /** @var ResourceKind */
    private $resourceKindWithWorkflow;
    /** @var ResourceKind */
    private $userGroup;
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
    /** @var ResourceEntity */
    private $resourceVisibleByGroup;
    /** @var ResourceEntity */
    private $allUsersGroup;
    /** @var  ResourceWorkflowPlace */
    private $workflowPlace1;
    /** @var  ResourceWorkflowPlace */
    private $workflowPlace2;
    /** @var ResourceWorkflowTransition */
    private $transition;

    protected function initializeDatabaseForTests() {
        parent::setUp();
        $this->clearDefaultLanguages();
        $this->createLanguage('PL', 'PL', 'polski'); //for validate parentMetadata
        $this->createLanguage('EN', 'EN', 'angielski'); //for validate parentMetadata
        /** @var MetadataRepository $metadataRepository */
        $metadataRepository = $this->container->get(MetadataRepository::class);
        $this->parentMetadata = $metadataRepository->findOne(SystemMetadata::PARENT);
        $this->metadata1 = $this->createMetadata('M1', ['PL' => 'metadata', 'EN' => 'metadata'], [], [], 'textarea');
        $this->metadata2 = $this->createMetadata('M2', ['PL' => 'metadata', 'EN' => 'metadata'], [], [], 'textarea');
        $this->metadata3 = $this->createMetadata(
            'M3',
            ['PL' => 'metadata', 'EN' => 'metadata'],
            [],
            [],
            'relationship',
            'books',
            ['resourceKind' => [-1]]
        );
        $this->metadata4 = $this->createMetadata(
            'M4',
            ['PL' => 'metadata', 'EN' => 'metadata'],
            [],
            [],
            'relationship',
            'books',
            ['resourceKind' => [-1]]
        );
        $this->metadata5 = $this->createMetadata('M5', ['PL' => 'metadata', 'EN' => 'metadata'], [], [], 'flexible-date');
        $this->metadata6 = $this->createMetadata(
            'M6',
            ['PL' => 'metadata', 'EN' => 'metadata'],
            [],
            [],
            'text',
            'books',
            ['uniqueInResourceClass' => true]
        );
        $this->addSupportForResourceKindToMetadata(SystemMetadata::REPRODUCTOR, SystemResourceKind::USER);
        $this->addSupportForResourceKindToMetadata(SystemMetadata::VISIBILITY, SystemResourceKind::USER);
        $this->addSupportForResourceKindToMetadata(SystemMetadata::TEASER_VISIBILITY, SystemResourceKind::USER);
        $this->workflowPlace1 = new ResourceWorkflowPlace(['PL' => 'key1', 'EN' => 'key1'], 'p1', [], [], [], [$this->metadata3->getId()]);
        $this->workflowPlace2 = new ResourceWorkflowPlace(['PL' => 'key2', 'EN' => 'key2'], 'p2', [], [], [], [$this->metadata4->getId()]);
        $this->transition = new ResourceWorkflowTransition(['PL' => 'key3', 'EN' => 'key3'], ['p1'], ['p2'], 't1');
        $workflow = $this->createWorkflow(
            ['PL' => 'Workflow', 'EN' => 'Workflow'],
            'books',
            [$this->workflowPlace1, $this->workflowPlace2],
            [$this->transition]
        );
        $sampleResourceWorkflowDriverFactory = new SampleResourceWorkflowDriverFactory($this->createMock(ResourceWorkflowDriver::class));
        $workflow = $sampleResourceWorkflowDriverFactory->setForWorkflow($workflow);
        $this->resourceKind = $this->createResourceKind(
            'Default RK',
            ['PL' => 'Resource kind', 'EN' => 'Resource kind'],
            [$this->metadata1, $this->metadata2, $this->metadata5, $this->metadata6]
        );
        $this->resourceKindWithWorkflow = $this->createResourceKind(
            'Default RK with workflow',
            ['PL' => 'Resource kind', 'EN' => 'Resource kind'],
            [$this->parentMetadata, $this->metadata1, $this->metadata3, $this->metadata4],
            false,
            $workflow
        );
        $this->resource = $this->createResource(
            $this->resourceKind,
            [
                $this->metadata1->getId() => ['Test value'],
                $this->metadata6->getId() => ['unique value'],
                SystemMetadata::VISIBILITY => [1],
                SystemMetadata::TEASER_VISIBILITY => [1],
            ]
        );
        $this->parentResource = $this->createResource(
            $this->resourceKind,
            [
                $this->metadata1->getId() => ['Test value for parent'],
                SystemMetadata::REPRODUCTOR => [1],
                SystemMetadata::VISIBILITY => [1],
                SystemMetadata::TEASER_VISIBILITY => [1],
            ]
        );
        $this->resourceWithWorkflow = $this->createResource(
            $this->resourceKindWithWorkflow,
            [
                $this->metadata1->getId() => ['Test value'],
                SystemMetadata::VISIBILITY => [1],
                SystemMetadata::TEASER_VISIBILITY => [1],
            ]
        );
        $this->childResource = $this->createResource(
            $this->resourceKind,
            [
                SystemMetadata::PARENT => [$this->parentResource->getId()],
                $this->metadata1->getId() => ['Test value for child'],
                SystemMetadata::VISIBILITY => [1],
                SystemMetadata::TEASER_VISIBILITY => [1],
            ]
        );
        $this->createUserGroup();
    }

    private function createUserGroup() {
        $groupNameMetadata = $this->createMetadata(
            'Group Name',
            ['PL' => 'Group Name', 'EN' => 'Group Name'],
            [],
            [],
            'text',
            'users'
        );
        $this->userGroup = $this->createResourceKind(
            'User group',
            ['PL' => 'User group', 'EN' => 'User group'],
            [$groupNameMetadata],
            false,
            null
        );
        $this->allUsersGroup = $this->createResource(
            $this->userGroup,
            [
                SystemMetadata::VISIBILITY => [1],
                SystemMetadata::TEASER_VISIBILITY => [1],
            ]
        );
        $this->addSupportForResourceKindToMetadata(SystemMetadata::VISIBILITY, $this->userGroup->getId());
        $this->addSupportForResourceKindToMetadata(SystemMetadata::TEASER_VISIBILITY, $this->userGroup->getId());
        $this->addSupportForResourceKindToMetadata(SystemMetadata::GROUP_MEMBER, $this->userGroup->getId());
        $this->resourceVisibleByGroup = $this->createResource(
            $this->resourceKind,
            [
                SystemMetadata::VISIBILITY => [$this->allUsersGroup->getId()]
            ]
        );
    }

    /** @small */
    public function testFetchingResources() {
        $client = self::createAdminClient();
        $client->apiRequest('GET', self::ENDPOINT, [], ['resourceClasses' => ['books']]);
        $this->assertStatusCode(200, $client->getResponse());
        $fetchedIds = array_column(json_decode($client->getResponse()->getContent(), true), 'id');
        $this->assertEquals(
            [
                $this->resourceVisibleByGroup->getId(),
                $this->childResource->getId(),
                $this->resourceWithWorkflow->getId(),
                $this->parentResource->getId(),
                $this->resource->getId()
            ],
            $fetchedIds
        );
        $this->assertEquals(5, $client->getResponse()->headers->get('pk_total'));
    }

    /** @small */
    public function testFetchingResourcesOrderByIdAsc() {
        $client = self::createAdminClient();
        $client->apiRequest(
            'GET',
            self::ENDPOINT,
            [],
            [
                'resourceClasses' => ['books'],
                'sortByIds' => [['columnId' => 'id', 'direction' => 'ASC', 'language' => 'PL']],
            ]
        );
        $this->assertStatusCode(200, $client->getResponse());
        $fetchedIds = array_column(json_decode($client->getResponse()->getContent(), true), 'id');
        $this->assertEquals(
            [
                $this->resource->getId(),
                $this->parentResource->getId(),
                $this->resourceWithWorkflow->getId(),
                $this->childResource->getId(),
                $this->resourceVisibleByGroup->getId()
            ],
            $fetchedIds
        );
        $this->assertEquals(5, $client->getResponse()->headers->get('pk_total'));
    }

    /** @small */
    public function testFetchingResourcesForFirstPageWithTwoByPage() {
        $client = self::createAdminClient();
        $client->apiRequest('GET', self::ENDPOINT, [], ['resourceClasses' => ['books'], 'page' => 1, 'resultsPerPage' => 2]);
        $this->assertStatusCode(200, $client->getResponse());
        $fetchedIds = array_column(json_decode($client->getResponse()->getContent(), true), 'id');
        $this->assertEquals([$this->resourceVisibleByGroup->getId(), $this->childResource->getId()], $fetchedIds);
        $this->assertEquals(5, $client->getResponse()->headers->get('pk_total'));
    }

    /** @small */
    public function testFetchingTopLevelResources() {
        $client = self::createAdminClient();
        $client->apiRequest('GET', self::ENDPOINT, [], ['resourceClasses' => ['books'], 'topLevel' => true]);
        $this->assertStatusCode(200, $client->getResponse());
        $fetchedIds = array_column(json_decode($client->getResponse()->getContent(), true), 'id');
        $this->assertEquals(
            [
                $this->resourceVisibleByGroup->getId(),
                $this->resourceWithWorkflow->getId(),
                $this->parentResource->getId(),
                $this->resource->getId()
            ],
            $fetchedIds
        );
        $this->assertEquals(4, $client->getResponse()->headers->get('pk_total'));
    }

    /** @small */
    public function testFetchingResourcesFailsWhenInvalidResourceClass() {
        $client = self::createAdminClient();
        $client->apiRequest('GET', self::ENDPOINT, [], ['resourceClasses' => ['resourceClass']]);
        $this->assertStatusCode(400, $client->getResponse());
    }

    /** @small */
    public function testFetchingSingleResource() {
        $client = self::createAdminClient();
        $client->apiRequest('GET', self::oneEntityEndpoint($this->resource->getId()));
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertJsonStringSimilarToArray(
            [
                'id' => $this->resource->getId(),
                'kindId' => $this->resourceKind->getId(),
                'contents' => ResourceContents::fromArray(
                    [
                        $this->metadata1->getId() => ['Test value'],
                        $this->metadata6->getId() => ['unique value'],
                        SystemMetadata::RESOURCE_LABEL => '#' . $this->resource->getId(),
                        SystemMetadata::VISIBILITY => $this->resource->getValues(SystemMetadata::VISIBILITY),
                        SystemMetadata::TEASER_VISIBILITY => $this->resource->getValues(SystemMetadata::TEASER_VISIBILITY),
                    ]
                )->toArray(),
                'resourceClass' => $this->resource->getResourceClass(),
                'displayStrategiesDirty' => false,
                'hasChildren' => false,
                'availableTransitions' => [SystemTransition::UPDATE()->toTransition($this->resourceKind, $this->resource)->toArray()],
                'isTeaser' => false,
                'canView' => true,
                'hasPendingUpdates' => false,
            ],
            $client->getResponse()->getContent()
        );
    }

    public function testFetchingSingleResourceByUnauthenticatedUser() {
        $client = self::createAdminClient();
        $client->apiRequest(
            'PUT',
            self::oneEntityEndpoint(SystemResource::UNAUTHENTICATED_USER),
            [
                'id' => SystemResource::UNAUTHENTICATED_USER,
                'kindId' => $this->userGroup->getId(),
                'contents' => [SystemMetadata::GROUP_MEMBER => [$this->allUsersGroup->getId()]],
            ]
        );
        $client = self::createClient();
        $client->apiRequest('GET', self::oneEntityEndpoint($this->resourceVisibleByGroup->getId()));
        $this->assertStatusCode(200, $client->getResponse());
    }

    public function testFetchingByParentId() {
        $client = self::createAdminClient();
        $client->apiRequest('GET', self::ENDPOINT, [], ['parentId' => $this->parentResource->getId()]);
        $this->assertStatusCode(200, $client->getResponse());
        $fetchedIds = array_column(json_decode($client->getResponse()->getContent(), true), 'id');
        $this->assertEquals([$this->childResource->getId()], $fetchedIds);
    }

    /** @small */
    public function testFetchingSortedResources() {
        $client = self::createAdminClient();
        $client->apiRequest(
            'GET',
            self::ENDPOINT,
            [],
            [
                'page' => 1,
                'resultsPerPage' => 2,
                'resourceClasses' => ['books'],
                'topLevel' => true,
                'sortByIds' => [0 => ['columnId' => $this->metadata1->getId(), 'direction' => 'DESC', 'language' => 'PL']],
            ]
        );
        $this->assertStatusCode(200, $client->getResponse());
        $expectedOrder = [$this->resourceVisibleByGroup->getId(), $this->parentResource->getId()];
        $actualOrder = array_column(json_decode($client->getResponse()->getContent(), true), 'id');
        $this->assertEquals($expectedOrder, $actualOrder);
        $this->assertEquals(4, $client->getResponse()->headers->get('pk_total'));
    }

    public function testCreatingResource(): int {
        $client = self::createAdminClient();
        $client->apiRequest(
            'POST',
            self::ENDPOINT,
            [
                'kindId' => $this->resourceKind->getId(),
                'contents' => [
                    $this->metadata1->getId() => ['created'],
                    SystemMetadata::VISIBILITY => [1],
                    SystemMetadata::TEASER_VISIBILITY => [1],
                ],
                'resourceClass' => 'books',
            ]
        );
        $this->assertStatusCode(201, $client->getResponse());
        $repository = self::createClient()->getContainer()->get(ResourceRepository::class);
        $createdId = json_decode($client->getResponse()->getContent())->id;
        /** @var ResourceEntity $created */
        $created = $repository->findOne($createdId);
        $this->assertEquals($this->resourceKind->getId(), $created->getKind()->getId());
        $this->assertEquals(['created'], $created->getValues($this->metadata1));
        $this->assertContains($created->getId(), $this->findResourceIdsByMetadataValue('created', $this->metadata1->getId()));
        return $createdId;
    }

    /** @depends testCreatingResource */
    public function testCreatingResourceSavesAuditEntry($createdId) {
        $auditEntries = $this->container->get(AuditEntryRepository::class)->findAll();
        $latestAuditEntry = end($auditEntries);
        $this->assertEquals('resource_create', $latestAuditEntry->getCommandName());
        $this->assertEquals($createdId, $latestAuditEntry->getData()['after']['resource']['id']);
        $this->assertArrayNotHasKey('before', $latestAuditEntry->getData());
    }

    public function testCreatingResourceWithWorkflow() {
        $client = self::createAdminClient();
        $client->apiRequest(
            'POST',
            self::ENDPOINT,
            [
                'kindId' => $this->resourceKindWithWorkflow->getId(),
                'contents' => [$this->metadata1->getId() => ['created']],
                'resourceClass' => 'books',
            ]
        );
        $this->assertStatusCode(201, $client->getResponse());
        $repository = self::createClient()->getContainer()->get(ResourceRepository::class);
        $createdId = json_decode($client->getResponse()->getContent())->id;
        /** @var ResourceEntity $created */
        $created = $repository->findOne($createdId);
        $this->assertEquals($this->resourceKindWithWorkflow->getId(), $created->getKind()->getId());
        $this->assertEquals(['created'], $created->getValues($this->metadata1));
        $this->assertEquals([1], $created->getContents()->getValuesWithoutSubmetadata($this->metadata3));
        $this->assertEquals(['p1' => true], $created->getMarking());
    }

    public function testCloningResourceWithUniqueMetadata() {
        $client = self::createAdminClient();
        $client->apiRequest(
            'POST',
            self::ENDPOINT,
            [
                'id' => $this->resource->getId(),
                'cloneTimes' => 23,
            ]
        );
        $this->assertStatusCode(201, $client->getResponse());
    }

    public function testCloningResourceWithoutWorkflow() {
        $client = self::createAdminClient();
        $client->apiRequest(
            'POST',
            self::ENDPOINT,
            [
                'id' => $this->resource->getId(),
                'cloneTimes' => 1,
            ]
        );
        $this->assertStatusCode(201, $client->getResponse());
    }

    public function testCloningResourceWithWorkflow() {
        $client = self::createAdminClient();
        $client->apiRequest(
            'POST',
            self::ENDPOINT,
            [
                'id' => $this->resourceWithWorkflow->getId(),
                'cloneTimes' => 1,
            ]
        );
        $this->assertStatusCode(201, $client->getResponse());
    }

    public function testCloningResourceWithEditedValues() {
        $client = self::createAdminClient();
        $newContents = $this->resource->getContents()->toArray();
        $newContents[$this->metadata2->getId()] = [['value' => 'new Test value']];
        $client->apiRequest(
            'POST',
            self::ENDPOINT,
            [
                'id' => $this->resource->getId(),
                'cloneTimes' => 1,
            ]
        );
        $this->assertStatusCode(201, $client->getResponse());
    }

    public function testCloningResourceWithParent() {
        $client = self::createAdminClient();
        $client->apiRequest(
            'POST',
            self::ENDPOINT,
            [
                'id' => $this->resource->getId(),
                'cloneTimes' => 1,
            ]
        );
        $this->assertStatusCode(201, $client->getResponse());
    }

    public function testCreatingResourceWithFlexibleDate() {
        $client = self::createAdminClient();
        $client->apiRequest(
            'POST',
            self::ENDPOINT,
            [
                'kindId' => $this->resourceKind->getId(),
                'contents' => [
                    $this->metadata5->getId() => [
                        [
                            'value' => [
                                'from' => '2018-09-13T16:39:49+02:00',
                                'to' => '2018-09-13T16:39:49+02:00',
                                'mode' => 'day',
                                'rangeMode' => null,
                            ],
                        ],
                    ],
                ],
                'resourceClass' => 'books',
            ]
        );
        $this->assertStatusCode(201, $client->getResponse());
    }

    public function testCreatingResourceWithRangeDateWithOneDateGiven() {
        $client = self::createAdminClient();
        $client->apiRequest(
            'POST',
            self::ENDPOINT,
            [
                'kindId' => $this->resourceKind->getId(),
                'contents' => [
                    $this->metadata5->getId() => [
                        [
                            'value' => [
                                'from' => '2013-09-13T16:39:49+02:00',
                                'mode' => 'range',
                                'rangeMode' => 'year',
                            ],
                        ],
                    ],
                ],
                'resourceClass' => 'books',
            ]
        );
        $this->assertStatusCode(201, $client->getResponse());
    }

    public function testIgnoringBullshitOrDeletedMetadataDuringEdit() {
        $client = self::createAdminClient();
        $client->apiRequest(
            'PUT',
            self::oneEntityEndpoint($this->resource->getId()),
            [
                'id' => $this->resource->getId(),
                'kindId' => $this->resourceKind->getId(),
                'contents' => [666 => ['edited']],
            ]
        );
        $this->assertStatusCode(200, $client->getResponse());
        $repository = self::createClient()->getContainer()->get(ResourceRepository::class);
        /** @var ResourceEntity $edited */
        $edited = $repository->findOne($this->resource->getId());
        $this->assertEmpty($edited->getValues(666));
    }

    public function testEditingResource() {
        $this->assertNotContains(
            $this->resource->getId(),
            $this->findResourceIdsByMetadataValue('edited', $this->metadata1->getId())
        );
        $client = self::createAdminClient();
        $client->apiRequest(
            'PUT',
            self::oneEntityEndpoint($this->resource->getId()),
            [
                'id' => $this->resource->getId(),
                'kindId' => $this->resourceKind->getId(),
                'contents' => [
                    $this->metadata1->getId() => ['edited'],
                    SystemMetadata::VISIBILITY => [1],
                    SystemMetadata::TEASER_VISIBILITY => [1],
                ],
            ]
        );
        $this->assertStatusCode(200, $client->getResponse());
        $repository = self::createClient()->getContainer()->get(ResourceRepository::class);
        /** @var ResourceEntity $edited */
        $edited = $repository->findOne($this->resource->getId());
        $this->assertEquals(['edited'], $edited->getValues($this->metadata1));
        $this->assertContains($edited->getId(), $this->findResourceIdsByMetadataValue('edited', $this->metadata1->getId()));
    }

    public function testAutoAssignMetadataWhenEditingResourceWithWorkflow() {
        $client = self::createAdminClient();
        $endpoint = self::oneEntityEndpoint($this->resourceWithWorkflow);
        $client->apiRequest(
            'PUT',
            $endpoint . '?' . http_build_query(['transitionId' => 't1']),
            [
                'id' => $this->resourceWithWorkflow->getId(),
                'kindId' => $this->resourceKindWithWorkflow->getId(),
                'contents' => [$this->metadata1->getId() => ['edited'], $this->metadata3->getId() => 1],
            ]
        );
        $this->assertStatusCode(200, $client->getResponse());
        $repository = self::createClient()->getContainer()->get(ResourceRepository::class);
        /** @var ResourceEntity $edited */
        $edited = $repository->findOne($this->resourceWithWorkflow->getId());
        $this->assertEquals(['edited'], $edited->getContents()->getValuesWithoutSubmetadata($this->metadata1));
        $this->assertEquals([1], $edited->getContents()->getValuesWithoutSubmetadata($this->metadata3));
        $this->assertEquals([1], $edited->getContents()->getValuesWithoutSubmetadata($this->metadata4));
    }

    public function testEditingResourceKindFails() {
        $newResourceKind = $this->createResourceKind(
            'Replacement resource kind',
            ['PL' => 'Replacement resource kind', 'EN' => 'Replacement resource kind'],
            [$this->parentMetadata, $this->metadata1, $this->metadata2]
        );
        $newResourceKind->getMetadataList()[0]->updateOrdinalNumber(0);
        $this->persistAndFlush($newResourceKind->getMetadataList()[1]);
        $client = self::createAdminClient();
        $client->apiRequest(
            'PUT',
            self::oneEntityEndpoint($this->resource->getId()),
            [
                'kindId' => $newResourceKind->getId(),
                'contents' => $this->resource->getContents(),
            ]
        );
        $this->assertStatusCode(200, $client->getResponse());
        $repository = self::createClient()->getContainer()->get(ResourceRepository::class);
        /** @var ResourceEntity $edited */
        $edited = $repository->findOne($this->resource->getId());
        $this->assertEquals($this->resourceKind->getId(), $edited->getKind()->getId());
    }

    public function testDeletingLeafResource() {
        $this->assertContains(
            $this->childResource->getId(),
            $this->findResourceIdsByMetadataValue($this->childResource->getValues($this->metadata1->getId())[0], $this->metadata1->getId())
        );
        $client = self::createAdminClient();
        $client->apiRequest('DELETE', self::oneEntityEndpoint($this->childResource->getId()));
        $this->assertStatusCode(204, $client->getResponse());
        /** @var ResourceRepository $repository */
        $repository = self::createClient()->getContainer()->get(ResourceRepository::class);
        $this->assertFalse($repository->exists($this->childResource->getId()));
        $this->assertNotContains(
            $this->childResource->getId(),
            $this->findResourceIdsByMetadataValue($this->childResource->getValues($this->metadata1->getId())[0], $this->metadata1->getId())
        );
    }

    public function testDeletingParentResourceIsForbidden() {
        $client = self::createAdminClient();
        $client->apiRequest('DELETE', self::oneEntityEndpoint($this->parentResource->getId()));
        $this->assertStatusCode(400, $client->getResponse());
        /** @var ResourceRepository $repository */
        $repository = self::createClient()->getContainer()->get(ResourceRepository::class);
        $this->assertTrue($repository->exists($this->parentResource->getId()));
    }

    public function testChangingResourceKindAsAdmin() {
        $client = $this->createAdminClient();
        $endpoint = self::oneEntityEndpoint($this->resource);
        $client->apiRequest(
            'PUT',
            $endpoint,
            [
                'id' => $this->resource->getId(),
                'newKindId' => $this->resourceKindWithWorkflow->getId(),
                'contents' => $this->resource->getContents(),
                'placesIds' => ['p1'],
            ],
            [],
            [],
            ['HTTP_GOD-EDIT' => true]
        );
        $this->assertStatusCode(200, $client->getResponse());
        $repository = self::createClient()->getContainer()->get(ResourceRepository::class);
        /** @var ResourceEntity $edited */
        $edited = $repository->findOne($this->resource->getId());
        $this->assertEquals($this->resourceKindWithWorkflow->getId(), $edited->getKind()->getId());
    }

    public function testChangingResourceKindToDifferentWorkflowAsAdmin() {
        $workflowPlace = new ResourceWorkflowPlace(['PL' => 'key1', 'EN' => 'key1'], 'p666');
        $workflow = $this->createWorkflow(
            ['PL' => 'Workflow 2', 'EN' => 'Workflow 2'],
            'books',
            [$workflowPlace],
            []
        );
        $resourceKindWithWorkflow = $this->createResourceKind(
            'Resource kind',
            ['PL' => 'Resource kind', 'EN' => 'Resource kind'],
            [$this->metadata1],
            false,
            $workflow
        );
        $client = $this->createAdminClient();
        $endpoint = self::oneEntityEndpoint($this->resourceWithWorkflow);
        $client->apiRequest(
            'PUT',
            $endpoint,
            [
                'id' => $this->resourceWithWorkflow->getId(),
                'newKindId' => $resourceKindWithWorkflow->getId(),
                'contents' => $this->resourceWithWorkflow->getContents(),
                'placesIds' => ['p1'],
            ],
            [],
            [],
            ['HTTP_GOD-EDIT' => true]
        );
        $this->assertStatusCode(200, $client->getResponse());
        $repository = self::createClient()->getContainer()->get(ResourceRepository::class);
        /** @var ResourceEntity $edited */
        $edited = $repository->findOne($this->resourceWithWorkflow->getId());
        $this->assertEquals($resourceKindWithWorkflow->getId(), $edited->getKind()->getId());
        $this->assertEquals('p666', $edited->getWorkflow()->getPlaces($edited)[0]->getId());
    }

    public function testChangingParentResource() {
        $client = $this->createAdminClient();
        $endpoint = self::oneEntityEndpoint($this->childResource);
        $client->apiRequest(
            'PUT',
            $endpoint,
            [
                'id' => $this->childResource->getId(),
                'newKindId' => $this->resourceKind->getId(),
                'contents' =>
                    [
                        -1 => [$this->resource->getId()],
                        $this->metadata1->getId() => ['Test value for child'],
                    ],
            ],
            [],
            [],
            ['HTTP_GOD-EDIT' => true]
        );
        $this->assertStatusCode(200, $client->getResponse());
        $repository = self::createClient()->getContainer()->get(ResourceRepository::class);
        /** @var ResourceEntity $edited */
        $edited = $repository->findOne($this->childResource->getId());
        $this->assertEquals($this->resource->getId(), $edited->getParentId());
    }

    public function testChangingPlaces() {
        $client = $this->createAdminClient();
        $endpoint = self::oneEntityEndpoint($this->resourceWithWorkflow);
        $client->apiRequest(
            'PUT',
            $endpoint . '?' . http_build_query(['placesIds' => [$this->workflowPlace2->getId()]]),
            [
                'id' => $this->resourceWithWorkflow->getId(),
                'kindId' => $this->resourceWithWorkflow->getId(),
                'contents' => $this->resourceWithWorkflow->getContents(),
            ],
            [],
            [],
            ['HTTP_GOD-EDIT' => true]
        );
        $this->assertStatusCode(200, $client->getResponse());
        $repository = self::createClient()->getContainer()->get(ResourceRepository::class);
        /** @var ResourceEntity $edited */
        $edited = $repository->findOne($this->resourceWithWorkflow->getId());
        $this->assertEquals([$this->workflowPlace2->getId() => 1], $edited->getMarking());
    }

    public function testEditPassWhenLackOfAdminRoles() {
        $this->executeCommand('repeka:create-user tester tester');
        $client = $this->createAuthenticatedClient('tester', 'tester');
        $endpoint = self::oneEntityEndpoint($this->resourceWithWorkflow);
        $client->apiRequest(
            'PUT',
            $endpoint,
            [
                'id' => $this->resourceWithWorkflow->getId(),
                'kindId' => $this->resourceWithWorkflow->getId(),
                'contents' => $this->resourceWithWorkflow->getContents(),
            ]
        );
        $this->assertStatusCode(200, $client->getResponse());
    }

    public function testGodEditFailsWhenLackOfAdminRoles() {
        $this->executeCommand('repeka:create-user tester tester');
        $client = $this->createAuthenticatedClient('tester', 'tester');
        $endpoint = self::oneEntityEndpoint($this->resourceWithWorkflow);
        $client->apiRequest(
            'PUT',
            $endpoint . '?' . http_build_query(['placesIds' => [$this->workflowPlace2->getId()]]),
            [
                'id' => $this->resourceWithWorkflow->getId(),
                'kindId' => $this->resourceWithWorkflow->getId(),
                'contents' => $this->resourceWithWorkflow->getContents(),
            ],
            [],
            [],
            ['HTTP_GOD-EDIT' => true]
        );
        $this->assertStatusCode(403, $client->getResponse());
    }

    private function findResourceIdsByMetadataValue($metadataValue, $metadataId): array {
        $query = new ResourceListFtsQuery($metadataValue, [$metadataId]);
        $results = $this->handleCommandBypassingFirewall($query);
        return EntityUtils::mapToIds($results);
    }
}
