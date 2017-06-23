<?php
namespace Repeka\Tests\Integration;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceKind;
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
        $baseMetadata1 = $this->createMetadata('Metadata', ['TEST' => 'Base metadata kind 1'], [], [], 'text');
        $baseMetadata2 = $this->createMetadata('Metadata', ['TEST' => 'Base metadata kind 2'], [], [], 'text');
        $this->resourceKind = $this->createResourceKind(['TEST' => 'Test'], [
            $this->resourceKindMetadata($baseMetadata1, ['TEST' => 'Metadata kind 1']),
            $this->resourceKindMetadata($baseMetadata2, ['TEST' => 'Metadata kind 2'])
        ]);
        $this->metadata1 = $this->resourceKind->getMetadataList()[0];
        $this->metadata2 = $this->resourceKind->getMetadataList()[1];
    }

    public function testFetchingResourceKinds() {
        $client = self::createAdminClient();
        $client->apiRequest('GET', self::ENDPOINT);
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
        $baseMetadata = Metadata::create('int', 'New base', ['TEST' => 'New base metadata']);
        $em->persist($baseMetadata);
        $em->flush();
        $client = self::createAdminClient();
        $client->apiRequest('POST', self::ENDPOINT, [
            'label' => ['TEST' => 'created'],
            'metadataList' => [[
                'baseId' => $baseMetadata->getId(),
                'control' => $baseMetadata->getControl(),
                'description' => [],
                'label' => ['TEST' => 'created'],
                'placeholder' => [],
                'shownInBrief' => false,
            ]]
        ]);
        $this->assertStatusCode(201, $client->getResponse());
        $resourceKindRepository = self::createClient()->getContainer()->get('repository.resource_kind');
        $createdId = json_decode($client->getResponse()->getContent())->id;
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
        $client->apiRequest('PATCH', self::joinUrl(self::ENDPOINT, $this->resourceKind->getId()), [
            'label' => ['TEST' => 'modified'],
            'metadataList' => [[
                'baseId' => $this->metadata2->getBaseId(),
                'control' => $this->metadata2->getControl(),
                'description' => $this->metadata2->getDescription(),
                'id' => $this->metadata2->getId(),
                'label' => $this->metadata2->getLabel(),
                'name' => $this->metadata2->getName(),
                'placeholder' => $this->metadata2->getPlaceholder(),
                'shownInBrief' => false,
            ], [
                'baseId' => $this->metadata1->getBaseId(),
                'control' => $this->metadata1->getControl(),
                'description' => $this->metadata1->getDescription(),
                'id' => $this->metadata1->getId(),
                'label' => $this->metadata1->getLabel(),
                'name' => $this->metadata1->getName(),
                'placeholder' => ['TEST' => 'modified'],
                'shownInBrief' => false,
            ]]
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $client = self::createClient();
        $resourceKindRepository = $client->getContainer()->get('repository.resource_kind');
        $metadataRepository = $client->getContainer()->get('repository.metadata');
        $resourceKind = $resourceKindRepository->findOne($this->resourceKind->getId());
        $metadata1 = $metadataRepository->findOne($this->metadata1->getId());
        $metadata2 = $metadataRepository->findOne($this->metadata2->getId());
        $this->assertEquals(['TEST' => 'modified'], $resourceKind->getLabel());
        $this->assertTrue($metadata2->getOrdinalNumber() < $metadata1->getOrdinalNumber());
        $this->assertEquals(['TEST' => 'modified'], $metadata1->getPlaceholder());
    }
}
