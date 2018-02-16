<?php
namespace Repeka\Tests\Integration;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
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
    /** @var ResourceKind */
    private $resourceKind;
    /** @var ResourceKind */
    private $resourceKindWithWorkflow;
    /** @var  Metadata */
    private $parentMetadata;
    /** @var ResourceEntity */
    private $resource;
    /** @var ResourceEntity */
    private $parentResource;
    /** @var ResourceEntity */
    private $childResource;

    protected function setUp() {
        parent::setUp();
        $this->clearDefaultLanguages();
        $this->createLanguage('TEST', 'te_ST', 'Test language');
        /** @var MetadataRepository $metadataRepository */
        $metadataRepository = $this->container->get(MetadataRepository::class);
        $this->parentMetadata = $metadataRepository->findOne(SystemMetadata::PARENT);
        $this->metadata1 = $this->createMetadata('M1', ['TEST' => 'metadata'], [], [], 'textarea');
        $this->metadata2 = $this->createMetadata('M2', ['TEST' => 'metadata'], [], [], 'textarea');
        $workflow = $this->createWorkflow(['TEST' => 'Workflow'], 'books', new ResourceWorkflowPlace(['key']));
        $sampleResourceWorkflowDriverFactory = new SampleResourceWorkflowDriverFactory($this->createMock(ResourceWorkflowDriver::class));
        $workflow = $sampleResourceWorkflowDriverFactory->setForWorkflow($workflow);
        $this->resourceKind = $this->createResourceKind(
            ['TEST' => 'Resource kind'],
            [$this->metadata1, $this->metadata2]
        );
        $this->resourceKindWithWorkflow = $this->createResourceKind(
            ['TEST' => 'Resource kind'],
            [$this->parentMetadata, $this->metadata1],
            [],
            $workflow
        );
        $this->resource = $this->createResource($this->resourceKind, [
            $this->metadata1->getId() => ['Test value'],
        ]);
        $this->parentResource = $this->createResource($this->resourceKind, [
            $this->metadata1->getId() => ['Test value for parent'],
        ]);
        $this->childResource = $this->createResource($this->resourceKind, [
            -1 => [$this->parentResource->getId()],
            $this->metadata1->getId() => ['Test value for child'],
        ]);
    }

    public function testFetchingResources() {
        $client = self::createAdminClient();
        $client->apiRequest('GET', self::ENDPOINT, [], ['resourceClasses' => 'books']);
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertJsonStringSimilarToArray([
            [
                'id' => $this->resource->getId(),
                'kindId' => $this->resourceKind->getId(),
                'contents' => [$this->metadata1->getId() => [['value' => 'Test value']]],
                'resourceClass' => $this->resource->getResourceClass(),
            ], [
                'id' => $this->parentResource->getId(),
                'kindId' => $this->resourceKind->getId(),
                'contents' => [$this->metadata1->getId() => [['value' => 'Test value for parent']]],
                'resourceClass' => $this->resource->getResourceClass(),
            ], [
                'id' => $this->childResource->getId(),
                'kindId' => $this->resourceKind->getId(),
                'contents' => [
                    $this->metadata1->getId() => [['value' => 'Test value for child']],
                    SystemMetadata::PARENT => [['value' => $this->parentResource->getId()]],
                ],
                'resourceClass' => $this->resource->getResourceClass(),
            ],
        ], $client->getResponse()->getContent());
    }

    public function testFetchingTopLevelResources() {
        $client = self::createAdminClient();
        $client->apiRequest('GET', self::ENDPOINT, [], ['resourceClasses' => 'books', 'topLevel' => true]);
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertJsonStringSimilarToArray([
            [
                'id' => $this->resource->getId(),
                'kindId' => $this->resourceKind->getId(),
                'contents' => ResourceContents::fromArray([$this->metadata1->getId() => ['Test value']])->toArray(),
                'resourceClass' => $this->resource->getResourceClass(),
            ], [
                'id' => $this->parentResource->getId(),
                'kindId' => $this->resourceKind->getId(),
                'contents' => ResourceContents::fromArray([$this->metadata1->getId() => ['Test value for parent']])->toArray(),
                'resourceClass' => $this->resource->getResourceClass(),
            ],
        ], $client->getResponse()->getContent());
    }

    public function testFetchingResourcesFailsWhenInvalidResourceClass() {
        $client = self::createAdminClient();
        $client->apiRequest('GET', self::ENDPOINT, [], ['resourceClasses' => 'resourceClass']);
        $this->assertStatusCode(400, $client->getResponse());
    }

    public function testFetchingSingleResource() {
        $client = self::createAdminClient();
        $client->apiRequest('GET', self::oneEntityEndpoint($this->resource->getId()));
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertJsonStringSimilarToArray([
            'id' => $this->resource->getId(),
            'kindId' => $this->resourceKind->getId(),
            'contents' => ResourceContents::fromArray([$this->metadata1->getId() => ['Test value']])->toArray(),
            'resourceClass' => $this->resource->getResourceClass(),
        ], $client->getResponse()->getContent());
    }

    public function testCreatingResource() {
        $client = self::createAdminClient();
        $client->apiRequest('POST', self::ENDPOINT, [
            'kindId' => $this->resourceKind->getId(),
            'contents' => json_encode([$this->metadata1->getId() => ['created']]),
            'resourceClass' => 'books',
        ]);
        $this->assertStatusCode(201, $client->getResponse());
        $repository = self::createClient()->getContainer()->get(ResourceRepository::class);
        $createdId = json_decode($client->getResponse()->getContent())->id;
        /** @var ResourceEntity $created */
        $created = $repository->findOne($createdId);
        $this->assertEquals($this->resourceKind->getId(), $created->getKind()->getId());
        $this->assertEquals(ResourceContents::fromArray([$this->metadata1->getId() => ['created']]), $created->getContents());
    }

    public function testCreatingResourceWithWorkflow() {
        $client = self::createAdminClient();
        $client->apiRequest('POST', self::ENDPOINT, [
            'kindId' => $this->resourceKindWithWorkflow->getId(),
            'contents' => json_encode([$this->metadata1->getId() => ['created']]),
            'resourceClass' => 'books',
        ]);
        $this->assertStatusCode(201, $client->getResponse());
        $repository = self::createClient()->getContainer()->get(ResourceRepository::class);
        $createdId = json_decode($client->getResponse()->getContent())->id;
        /** @var ResourceEntity $created */
        $created = $repository->findOne($createdId);
        $this->assertEquals($this->resourceKindWithWorkflow->getId(), $created->getKind()->getId());
        $this->assertEquals(ResourceContents::fromArray([$this->metadata1->getId() => ['created']]), $created->getContents());
        $this->assertEquals(['key' => true], $created->getMarking());
    }

    public function testEditingResource() {
        $client = self::createAdminClient();
        $client->apiRequest('POST', self::oneEntityEndpoint($this->resource->getId()), [
            'id' => $this->resource->getId(),
            'kindId' => $this->resourceKind->getId(),
            'contents' => json_encode([$this->metadata1->getId() => ['edited']]),
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $repository = self::createClient()->getContainer()->get(ResourceRepository::class);
        /** @var ResourceEntity $edited */
        $edited = $repository->findOne($this->resource->getId());
        $this->assertEquals(ResourceContents::fromArray([$this->metadata1->getId() => ['edited']]), $edited->getContents());
    }

    public function testEditingResourceKindFails() {
        $newResourceKind = $this->createResourceKind(
            ['TEST' => 'Replacement resource kind'],
            [$this->parentMetadata, $this->metadata1, $this->metadata2]
        );
        $newResourceKind->getMetadataList()[0]->updateOrdinalNumber(0);
        $this->persistAndFlush($newResourceKind->getMetadataList()[1]);
        $client = self::createAdminClient();
        $client->apiRequest('POST', self::oneEntityEndpoint($this->resource->getId()), [
            'kindId' => $newResourceKind->getId(),
            'contents' => json_encode($this->resource->getContents()),
        ]);
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
