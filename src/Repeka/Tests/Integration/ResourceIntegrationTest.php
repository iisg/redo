<?php
namespace Repeka\Tests\Integration;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Tests\IntegrationTestCase;

class ResourceIntegrationTest extends IntegrationTestCase {
    const ENDPOINT = '/api/resources';

    /** @var Metadata */
    private $baseMetadata;
    /** @var ResourceKind */
    private $resourceKind;
    /** @var Metadata */
    private $metadata;
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
        $this->baseMetadata = $this->createMetadata('Base', ['TEST' => 'Base metadata'], [], [], 'text');
        $this->resourceKind = $this->createResourceKind(['TEST' => 'Resource kind'], [
            $this->resourceKindMetadata($this->baseMetadata, ['TEST' => 'Metadata'])
        ]);
        $this->metadata = $this->resourceKind->getMetadataList()[0];
        $this->metadata->updateOrdinalNumber(0);
        $this->persistAndFlush($this->metadata);
        $this->resource = $this->createResource($this->resourceKind, [
            $this->baseMetadata->getId() => ['Test value']
        ]);
        $this->parentResource = $this->createResource($this->resourceKind, [
            $this->baseMetadata->getId() => ['Test value for parent'],
        ]);
        $this->childResource = $this->createResource($this->resourceKind, [
            -1 => [$this->parentResource->getId()],
            $this->baseMetadata->getId() => ['Test value for child'],
        ]);
    }

    public function testFetchingResources() {
        $client = self::createAdminClient();
        $client->apiRequest('GET', self::ENDPOINT);
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertJsonStringSimilarToArray([[
            'id' => $this->resource->getId(),
            'kindId' => $this->resourceKind->getId(),
            'contents' => [$this->metadata->getBaseId() => ['Test value']]
        ], [
            'id' => $this->parentResource->getId(),
            'kindId' => $this->resourceKind->getId(),
            'contents' => [$this->metadata->getBaseId() => ['Test value for parent']],
        ], [
            'id' => $this->childResource->getId(),
            'kindId' => $this->resourceKind->getId(),
            'contents' => [
                -1 => [$this->parentResource->getId()],
                $this->metadata->getBaseId() => ['Test value for child'],
            ],
        ],], $client->getResponse()->getContent());
    }

    public function testFetchingSingleResource() {
        $client = self::createAdminClient();
        $client->apiRequest('GET', self::oneEntityEndpoint($this->resource->getId()));
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertJsonStringSimilarToArray([
            'id' => $this->resource->getId(),
            'kindId' => $this->resourceKind->getId(),
            'contents' => [$this->metadata->getBaseId() => ['Test value']]
        ], $client->getResponse()->getContent());
    }

    public function testCreatingResource() {
        $client = self::createAdminClient();
        $client->apiRequest('POST', self::ENDPOINT, [
            'kind_id' => $this->resourceKind->getId(),
            'contents' => json_encode([$this->metadata->getBaseId() => ['created']])
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
            'kind_id' => $this->resourceKind->getId(),
            'contents' => json_encode([$this->metadata->getBaseId() => ['edited']])
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $repository = self::createClient()->getContainer()->get(ResourceRepository::class);
        /** @var ResourceEntity $edited */
        $edited = $repository->findOne($this->resource->getId());
        $this->assertEquals([$this->metadata->getBaseId() => ['edited']], $edited->getContents());
    }

    public function testEditingResourceKindFails() {
        $newResourceKind = $this->createResourceKind(['TEST' => 'Replacement resource kind'], [
            $this->resourceKindMetadata($this->baseMetadata, ['TEST' => 'Metadata'])
        ]);
        $newResourceKind->getMetadataList()[0]->updateOrdinalNumber(0);
        $this->persistAndFlush($newResourceKind->getMetadataList()[0]);
        $client = self::createAdminClient();
        $client->apiRequest('POST', self::oneEntityEndpoint($this->resource->getId()), [
            'kind_id' => $newResourceKind->getId(),
            'contents' => json_encode($this->resource->getContents())
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
