<?php
namespace Repeka\Tests\Integration;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceContents;
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
    /** @var  MetadataRepository */
    private $metadataRepository;

    public function setUp() {
        parent::setUp();
        $this->clearDefaultLanguages();
        $this->createLanguage('TEST', 'te_ST', 'Test language');
        $this->metadataRepository = $this->container->get(MetadataRepository::class);
        $parentMetadata = $this->metadataRepository->findOne(SystemMetadata::PARENT);
        $baseMetadata1 = $this->createMetadata('Metadata', ['TEST' => 'Base metadata kind 1'], [], [], MetadataControl::TEXTAREA());
        $baseMetadata2 = $this->createMetadata('Metadata', ['TEST' => 'Base metadata kind 2'], [], [], MetadataControl::TEXTAREA());
        $baseDictionaryMetadata = $this->createMetadata(
            'Metadata',
            ['TEST' => 'Base metadata dictionary'],
            [],
            [],
            MetadataControl::TEXTAREA,
            'dictionaries'
        );
        $this->resourceKind = $this->createResourceKind(['TEST' => 'Test'], [$parentMetadata, $baseMetadata1, $baseMetadata2]);
        $this->createResourceKind(['TEST' => 'Test'], [$parentMetadata, $baseDictionaryMetadata]);
        $this->metadata1 = $this->resourceKind->getMetadataList()[1];
        $this->metadata2 = $this->resourceKind->getMetadataList()[2];
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
        $this->assertCount(3, $responseItem->metadataList);
        $sortedMetadata = ($responseItem->metadataList[0]->id == $this->metadata1->getId())
            ? $responseItem->metadataList
            : array_reverse($responseItem->metadataList);
        $this->assertEquals($this->metadata1->getId(), $sortedMetadata[1]->id);
        $this->assertEquals($this->metadata2->getId(), $sortedMetadata[0]->id);
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
        $this->assertCount(3, $responseItem->metadataList);
        $sortedMetadata = ($responseItem->metadataList[0]->id == $this->metadata1->getId())
            ? $responseItem->metadataList
            : array_reverse($responseItem->metadataList);
        $this->assertEquals($this->metadata1->getId(), $sortedMetadata[1]->id);
        $this->assertEquals($this->metadata2->getId(), $sortedMetadata[0]->id);
    }

    public function testCreatingResourceKind() {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $metadata = $this->createMetadata('New base', ['TEST' => 'New base metadata'], [], [], MetadataControl::TEXTAREA());
        $em->persist($metadata);
        $em->flush();
        $client = self::createAdminClient();
        $client->apiRequest(
            'POST',
            self::ENDPOINT,
            [
                'label' => ['TEST' => 'created'],
                'metadataList' => [
                    ['id' => $metadata->getId(), 'label' => ['TEST' => 'created overridden']],
                ],
            ]
        );
        $this->assertStatusCode(201, $client->getResponse());
        $resourceKindRepository = self::createClient()->getContainer()->get(ResourceKindRepository::class);
        $createdId = json_decode($client->getResponse()->getContent())->id;
        /** @var ResourceKind $createdResourceKind */
        $createdResourceKind = $resourceKindRepository->findOne($createdId);
        $this->assertEquals(['TEST' => 'created'], $createdResourceKind->getLabel());
        $this->assertCount(2, $createdResourceKind->getMetadataList());
        $assignedMetadata = $createdResourceKind->getMetadataById($metadata->getId());
        $this->assertEquals($metadata->getControl(), $assignedMetadata->getControl());
        $this->assertEquals($metadata->getId(), $assignedMetadata->getId());
        $this->assertEquals($metadata->getName(), $assignedMetadata->getName());
        $this->assertEquals(['TEST' => 'created overridden'], $assignedMetadata->getLabel());
    }

    public function testEditingResourceKind() {
        $client = self::createAdminClient();
        $client->apiRequest(
            'PATCH',
            self::oneEntityEndpoint($this->resourceKind->getId()),
            [
                'label' => ['TEST' => 'modified'],
                'metadataList' => [
                    ['id' => $this->metadata2->getId()],
                    ['id' => $this->metadata1->getId(), 'placeholder' => ['TEST' => 'modified']],
                    ['id' => SystemMetadata::PARENT],
                ],
                'displayStrategies' => [],
            ]
        );
        $this->assertStatusCode(200, $client->getResponse());
        $client = self::createClient();
        $resourceKindRepository = $client->getContainer()->get(ResourceKindRepository::class);
        /** @var ResourceKind $resourceKind */
        $resourceKind = $resourceKindRepository->findOne($this->resourceKind->getId());
        $this->assertEquals(['TEST' => 'modified'], $resourceKind->getLabel());
        $this->assertEquals(
            [$this->metadata2->getId(), $this->metadata1->getId(), SystemMetadata::PARENT],
            $resourceKind->getMetadataIds()
        );
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
        $this->assertTrue($metadataRepository->exists($this->metadata1->getId()));
        $this->assertTrue($metadataRepository->exists($this->metadata2->getId()));
    }

    public function testDeletingUsedResourceKindFails() {
        $this->handleCommand(
            new ResourceCreateCommand(
                $this->resourceKind,
                ResourceContents::fromArray(
                    [
                        $this->metadata1->getId() => ['test1'],
                        $this->metadata2->getId() => ['test2'],
                    ]
                )
            )
        );
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
