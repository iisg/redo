<?php
namespace Repeka\Tests\Integration;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Tests\IntegrationTestCase;

class ResourceIntegrationTest extends IntegrationTestCase {
    const ENDPOINT = '/api/resources';

    /** @var Metadata */
    private $baseMetadata2;
    /** @var Metadata */
    private $baseMetadata;
    /** @var ResourceKind */
    private $resourceKind;
    /** @var Metadata */
    private $metadata;
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
        $this->baseMetadata = $this->createMetadata('Base', ['TEST' => 'Base metadata'], [], [], 'textarea');
        $this->baseMetadata2 = $this->createMetadata('Base2', ['TEST' => 'Base metadata'], [], [], 'textarea');
        $this->resourceKind = $this->createResourceKind(['TEST' => 'Resource kind'], [
            $this->resourceKindMetadata($this->parentMetadata, ['TEST' => 'Metadata']),
            $this->resourceKindMetadata($this->baseMetadata, ['TEST' => 'Metadata']),
            $this->resourceKindMetadata($this->baseMetadata2, ['TEST' => 'Metadata']),
        ]);
        $this->metadata = $this->resourceKind->getMetadataList()[1];
        $this->metadata->updateOrdinalNumber(0);
        $this->persistAndFlush($this->metadata);
        $this->resource = $this->createResource($this->resourceKind, [
            $this->baseMetadata->getId() => ['Test value'],
        ], 'books');
        $this->parentResource = $this->createResource($this->resourceKind, [
            $this->baseMetadata->getId() => ['Test value for parent'],
        ], 'books');
        $this->childResource = $this->createResource($this->resourceKind, [
            -1 => [$this->parentResource->getId()],
            $this->baseMetadata->getId() => ['Test value for child'],
        ], 'books');
    }

    public function testFetchingResources() {
        $client = self::createAdminClient();
        $client->apiRequest('GET', self::ENDPOINT, [], ['resourceClasses' => 'books']);
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertJsonStringSimilarToArray([
            [
                'id' => $this->resource->getId(),
                'kindId' => $this->resourceKind->getId(),
                'contents' => [$this->metadata->getBaseId() => ['Test value']],
                'resourceClass' => $this->resource->getResourceClass(),
            ], [
                'id' => $this->parentResource->getId(),
                'kindId' => $this->resourceKind->getId(),
                'contents' => [$this->metadata->getBaseId() => ['Test value for parent']],
                'resourceClass' => $this->resource->getResourceClass(),
            ], [
                'id' => $this->childResource->getId(),
                'kindId' => $this->resourceKind->getId(),
                'contents' => [$this->metadata->getBaseId() => ['Test value for child'], -1 => [$this->parentResource->getId()]],
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
                'contents' => [$this->metadata->getBaseId() => ['Test value']],
                'resourceClass' => $this->resource->getResourceClass(),
            ], [
                'id' => $this->parentResource->getId(),
                'kindId' => $this->resourceKind->getId(),
                'contents' => [$this->metadata->getBaseId() => ['Test value for parent']],
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
            'contents' => [$this->metadata->getBaseId() => ['Test value']],
            'resourceClass' => $this->resource->getResourceClass(),
        ], $client->getResponse()->getContent());
    }

    public function testCreatingResource() {
        $client = self::createAdminClient();
        $client->apiRequest('POST', self::ENDPOINT, [
            'kindId' => $this->resourceKind->getId(),
            'contents' => json_encode([$this->metadata->getBaseId() => ['created']]),
            'resourceClass' => 'books',
        ]);
        $this->assertStatusCode(201, $client->getResponse());
        $repository = self::createClient()->getContainer()->get(ResourceRepository::class);
        $createdId = json_decode($client->getResponse()->getContent())->id;
        /** @var ResourceEntity $created */
        $created = $repository->findOne($createdId);
        $this->assertEquals($this->resourceKind->getId(), $created->getKind()->getId());
        $this->assertEquals([$this->metadata->getBaseId() => ['created']], $created->getContents());
    }

    public function testEditingResource() {
        $client = self::createAdminClient();
        $client->apiRequest('POST', self::oneEntityEndpoint($this->resource->getId()), [
            'id' => $this->resource->getId(),
            'kindId' => $this->resourceKind->getId(),
            'contents' => json_encode([$this->metadata->getBaseId() => ['edited']]),
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $repository = self::createClient()->getContainer()->get(ResourceRepository::class);
        /** @var ResourceEntity $edited */
        $edited = $repository->findOne($this->resource->getId());
        $this->assertEquals([$this->metadata->getBaseId() => ['edited']], $edited->getContents());
    }

    public function testEditingResourceKindFails() {
        $newResourceKind = $this->createResourceKind(['TEST' => 'Replacement resource kind'], [
            $this->resourceKindMetadata($this->parentMetadata, ['TEST' => 'Metadata']),
            $this->resourceKindMetadata($this->baseMetadata, ['TEST' => 'Metadata']),
            $this->resourceKindMetadata($this->baseMetadata2, ['TEST' => 'Metadata']),
        ]);
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

    public function testDeletingParentResource() {
        $client = self::createAdminClient();
        $client->apiRequest('DELETE', self::oneEntityEndpoint($this->parentResource->getId()));
        $this->assertStatusCode(400, $client->getResponse());
        /** @var ResourceRepository $repository */
        $repository = self::createClient()->getContainer()->get(ResourceRepository::class);
        $this->assertTrue($repository->exists($this->parentResource->getId()));
    }
}
