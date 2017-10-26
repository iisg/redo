<?php
namespace Repeka\Tests\Integration;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommand;
use Repeka\Tests\IntegrationTestCase;

class ResourceKindIntegrationTest extends IntegrationTestCase {
    const ENDPOINT = '/api/resource-kinds';

    /** @var ResourceKind */
    private $resourceKind;
    /** @var Metadata */
    private $metadata1;
    /** @var Metadata */
    private $metadata2;

    public function setUp() {
        parent::setUp();
        $this->clearDefaultLanguages();
        $this->createLanguage('TEST', 'te_ST', 'Test language');
        $baseMetadata1 = $this->createMetadata('Metadata', ['TEST' => 'Base metadata kind 1'], [], [], 'text', 'books');
        $baseMetadata2 = $this->createMetadata('Metadata', ['TEST' => 'Base metadata kind 2'], [], [], 'text', 'books');
        $baseDictionaryMetadata = $this->createMetadata('Metadata', ['TEST' => 'Base metadata dictionary'], [], [], 'text', 'dictionaries');
        $this->resourceKind = $this->createResourceKind(['TEST' => 'Test'], [
            $this->resourceKindMetadata($baseMetadata1, ['TEST' => 'Metadata kind 1'], 'books'),
            $this->resourceKindMetadata($baseMetadata2, ['TEST' => 'Metadata kind 2'], 'books')
        ], 'books');
        $this->createResourceKind(['TEST' => 'Test'], [
            $this->resourceKindMetadata($baseDictionaryMetadata, ['TEST' => 'Metadata kind dictionary'], 'dictionaries')
        ], 'dictionaries');
        $this->metadata1 = $this->resourceKind->getMetadataList()[0];
        $this->metadata2 = $this->resourceKind->getMetadataList()[1];
    }

    public function testFetchingAllResourceKinds() {
        $client = self::createAdminClient();
        $client->apiRequest('GET', self::ENDPOINT, [], ['allResourceKinds' => true]);
        $this->assertStatusCode(200, $client->getResponse());
        $responseContent = json_decode($client->getResponse()->getContent());
        $this->assertCount(3, $responseContent);
        $responseItem = $responseContent[1];
        $this->assertEquals($this->resourceKind->getId(), $responseItem->id);
        $this->assertEquals($this->resourceKind->getLabel(), self::objectToArray($responseItem->label));
        $this->assertCount(2, $responseItem->metadataList);
        $sortedMetadata = ($responseItem->metadataList[0]->id == $this->metadata1->getId())
            ? $responseItem->metadataList
            : array_reverse($responseItem->metadataList);
        $this->assertEquals($this->metadata1->getId(), $sortedMetadata[0]->id);
        $this->assertEquals($this->metadata2->getId(), $sortedMetadata[1]->id);
    }

    public function testFetchingResourceKindsWithResourceClass() {
        $client = self::createAdminClient();
        $client->apiRequest('GET', self::ENDPOINT, [], ['resourceClass' => 'books']);
        $this->assertStatusCode(200, $client->getResponse());
        $responseContent = json_decode($client->getResponse()->getContent());
        $this->assertCount(1, $responseContent);
        $responseItem = $responseContent[0];
        $this->assertEquals($this->resourceKind->getId(), $responseItem->id);
        $this->assertEquals($this->resourceKind->getLabel(), self::objectToArray($responseItem->label));
        $this->assertCount(2, $responseItem->metadataList);
        $sortedMetadata = ($responseItem->metadataList[0]->id == $this->metadata1->getId())
            ? $responseItem->metadataList
            : array_reverse($responseItem->metadataList);
        $this->assertEquals($this->metadata1->getId(), $sortedMetadata[0]->id);
        $this->assertEquals($this->metadata2->getId(), $sortedMetadata[1]->id);
    }

    public function testCreatingResourceKind() {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $baseMetadata = Metadata::create(MetadataControl::INTEGER(), 'New base', ['TEST' => 'New base metadata'], 'books');
        $em->persist($baseMetadata);
        $em->flush();
        $client = self::createAdminClient();
        $client->apiRequest('POST', self::ENDPOINT, [
            'label' => ['TEST' => 'created'],
            'metadataList' => [[
                'baseId' => $baseMetadata->getId(),
                'control' => $baseMetadata->getControl()->getValue(),
                'description' => [],
                'label' => ['TEST' => 'created'],
                'placeholder' => [],
                'shownInBrief' => false,
                'resourceClass' => 'books'
            ]],
            'resourceClass' => 'books'
        ]);
        $this->assertStatusCode(201, $client->getResponse());
        $resourceKindRepository = self::createClient()->getContainer()->get(ResourceKindRepository::class);
        $createdId = json_decode($client->getResponse()->getContent())->id;
        /** @var ResourceKind $createdResourceKind */
        $createdResourceKind = $resourceKindRepository->findOne($createdId);
        $this->assertEquals(['TEST' => 'created'], $createdResourceKind->getLabel());
        $this->assertCount(1, $createdResourceKind->getMetadataList());
        $createdMetadata = $createdResourceKind->getMetadataList()[0];
        $this->assertEquals($baseMetadata->getControl(), $createdMetadata->getControl());
        $this->assertEquals($baseMetadata->getId(), $createdMetadata->getBaseId());
        $this->assertEquals($baseMetadata->getName(), $createdMetadata->getName());
        $this->assertEquals(['TEST' => 'created'], $createdMetadata->getLabel());
    }

    public function testEditingResourceKind() {
        $client = self::createAdminClient();
        $client->apiRequest('PATCH', self::oneEntityEndpoint($this->resourceKind->getId()), [
            'label' => ['TEST' => 'modified'],
            'metadataList' => [[
                'baseId' => $this->metadata2->getBaseId(),
                'control' => $this->metadata2->getControl()->getValue(),
                'description' => $this->metadata2->getDescription(),
                'id' => $this->metadata2->getId(),
                'label' => $this->metadata2->getLabel(),
                'name' => $this->metadata2->getName(),
                'placeholder' => $this->metadata2->getPlaceholder(),
                'shownInBrief' => false,
                'resourceClass' => 'books',
            ], [
                'baseId' => $this->metadata1->getBaseId(),
                'control' => $this->metadata1->getControl()->getValue(),
                'description' => $this->metadata1->getDescription(),
                'id' => $this->metadata1->getId(),
                'label' => $this->metadata1->getLabel(),
                'name' => $this->metadata1->getName(),
                'placeholder' => ['TEST' => 'modified'],
                'shownInBrief' => false,
                'resourceClass' => 'books',
            ]],
            'resourceClass' => 'books',
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $client = self::createClient();
        $resourceKindRepository = $client->getContainer()->get(ResourceKindRepository::class);
        $metadataRepository = $client->getContainer()->get(MetadataRepository::class);
        /** @var ResourceKind $resourceKind */
        $resourceKind = $resourceKindRepository->findOne($this->resourceKind->getId());
        /** @var Metadata $metadata1 */
        $metadata1 = $metadataRepository->findOne($this->metadata1->getId());
        /** @var Metadata $metadata2 */
        $metadata2 = $metadataRepository->findOne($this->metadata2->getId());
        $this->assertEquals(['TEST' => 'modified'], $resourceKind->getLabel());
        $this->assertTrue($metadata2->getOrdinalNumber() < $metadata1->getOrdinalNumber());
        $this->assertEquals(['TEST' => 'modified'], $metadata1->getPlaceholder());
    }

    public function testDeletingResourceKind() {
        $client = self::createAdminClient();
        $client->apiRequest('DELETE', self::oneEntityEndpoint($this->resourceKind->getId()));
        $this->assertStatusCode(204, $client->getResponse());
        $client = self::createClient();
        /** @var ResourceKindRepository $resourceKindRepository */
        $resourceKindRepository = $client->getContainer()->get(ResourceKindRepository::class);
        /** @var MetadataRepository $metadataRepository */
        $metadataRepository = $client->getContainer()->get(MetadataRepository::class);
        $this->assertFalse($resourceKindRepository->exists($this->resourceKind->getId()));
        $this->assertFalse($metadataRepository->exists($this->metadata1->getId()));
        $this->assertFalse($metadataRepository->exists($this->metadata2->getId()));
    }

    public function testDeletingUsedResourceKindFails() {
        $this->handleCommand(new ResourceCreateCommand($this->resourceKind, [
            $this->metadata1->getBaseId() => ['test1'],
            $this->metadata2->getBaseId() => ['test2'],
        ], 'books'));
        $client = self::createAdminClient();
        $client->apiRequest('DELETE', self::oneEntityEndpoint($this->resourceKind->getId()));
        $this->assertStatusCode(400, $client->getResponse());
        $client = self::createClient();
        /** @var ResourceKindRepository $resourceKindRepository */
        $resourceKindRepository = $client->getContainer()->get(ResourceKindRepository::class);
        /** @var MetadataRepository $metadataRepository */
        $metadataRepository = $client->getContainer()->get(MetadataRepository::class);
        $this->assertTrue($resourceKindRepository->exists($this->resourceKind->getId()));
        $this->assertTrue($metadataRepository->exists($this->metadata1->getId()));
        $this->assertTrue($metadataRepository->exists($this->metadata2->getId()));
    }

    public function testFetchingResourceKindsFailsWhenInvalidResourceClass() {
        $client = self::createAdminClient();
        $client->apiRequest('GET', self::ENDPOINT, [], ['resourceClass' => 'resourceClass']);
        $this->assertStatusCode(400, $client->getResponse());
    }
}
