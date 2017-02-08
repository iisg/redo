<?php
namespace Repeka\Tests\Integration;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
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

    public function setUp() {
        parent::setUp();
        $this->createLanguage('TEST', 'te_ST', 'Test language');
        $this->baseMetadata = $this->createMetadata('Base', ['TEST' => 'Base metadata'], [], [], 'text');
        $this->resourceKind = $this->createResourceKind(['TEST' => 'Resource kind'], [
            $this->resourceKindMetadata($this->baseMetadata, ['TEST' => 'Metadata'])
        ]);
        $this->metadata = $this->resourceKind->getMetadataList()[0];
        $this->metadata->updateOrdinalNumber(0);
        $this->persistAndFlush($this->metadata);
        $this->resource = $this->createResource($this->resourceKind, [$this->baseMetadata->getId() => 'Test value']);
    }

    public function testFetchingResources() {
        $this->markTestSkipped('Enabled in the next commit');
        $client = self::createAdminClient();
        $client->apiRequest('GET', self::ENDPOINT);
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertJsonStringSimilarToArray([[
            'id' => $this->resource->getId(),
            'kindId' => $this->resourceKind->getId(),
            'contents' => [$this->metadata->getBaseId() => 'Test value']
        ]], $client->getResponse()->getContent());
    }

    public function testFetchingSingleResource() {
        $this->markTestSkipped('Enabled in the next commit');
        $client = self::createAdminClient();
        $client->apiRequest('GET', self::joinUrl(self::ENDPOINT, $this->resource->getId()));
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertJsonStringSimilarToArray([
            'id' => $this->resource->getId(),
            'kindId' => $this->resourceKind->getId(),
            'contents' => [$this->metadata->getBaseId() => 'Test value']
        ], $client->getResponse()->getContent());
    }

    public function testCreatingResource() {
        $client = self::createAdminClient();
        $client->apiRequest('POST', self::ENDPOINT, [
            'kind_id' => $this->resourceKind->getId(),
            'contents' => [$this->metadata->getBaseId() => 'created']
        ]);
        $this->assertStatusCode(201, $client->getResponse());
        $repository = self::createClient()->getContainer()->get('repository.resource');
        $createdId = json_decode($client->getResponse()->getContent())->id;
        $created = $repository->findOne($createdId);
        $this->assertEquals($this->resourceKind->getId(), $created->getKind()->getId());
        $this->assertEquals([$this->metadata->getBaseId() => 'created'], $created->getContents());
    }

    public function testEditingResource() {
        $client = self::createAdminClient();
        $client->apiRequest('PUT', self::joinUrl(self::ENDPOINT, $this->resource->getId()), [
            'id' => $this->resource->getId(),
            'kind_id' => $this->resourceKind->getId(),
            'contents' => [$this->metadata->getBaseId() => 'edited']
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $repository = self::createClient()->getContainer()->get('repository.resource');
        $edited = $repository->findOne($this->resource->getId());
        $this->assertEquals([$this->metadata->getBaseId() => 'edited'], $edited->getContents());
    }

    public function testEditingResourceKindFails() {
        $newResourceKind = $this->createResourceKind(['TEST' => 'Replacement resource kind'], [
            $this->resourceKindMetadata($this->baseMetadata, ['TEST' => 'Metadata'])
        ]);
        $newResourceKind->getMetadataList()[0]->updateOrdinalNumber(0);
        $this->persistAndFlush($newResourceKind->getMetadataList()[0]);
        $client = self::createAdminClient();
        $client->apiRequest('PUT', self::joinUrl(self::ENDPOINT, $this->resource->getId()), [
            'kind_id' => $newResourceKind->getId(),
            'contents' => $this->resource->getContents()
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $repository = self::createClient()->getContainer()->get('repository.resource');
        $edited = $repository->findOne($this->resource->getId());
        $this->assertEquals($this->resourceKind->getId(), $edited->getKind()->getId());
    }
}
