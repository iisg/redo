<?php
namespace Repeka\Tests\Integration;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Tests\IntegrationTestCase;

class MetadataIntegrationTest extends IntegrationTestCase {
    const ENDPOINT = '/api/metadata';

    public function setUp() {
        parent::setUp();
        $this->clearDefaultLanguages();
    }

    public function testFetchingMetadataKinds() {
        $this->createLanguage('TEST', 'TE', 'Test language');
        $metadata1 = $this->createMetadata('Metadata1', ['TEST' => 'First'], [], [], 'text', 'books');
        $metadata2 = $this->createMetadata('Metadata2', ['TEST' => 'Second'], ['TEST' => 'Hello'], ['TEST' => 'World'], 'integer', 'books');
        $client = self::createAdminClient();
        $client->apiRequest('GET', self::ENDPOINT, [], ['resourceClass' => 'books']);
        $this->assertStatusCode(200, $client->getResponse());
        $responseContent = $client->getResponse()->getContent();
        $this->assertJsonStringSimilarToArray([[
            'id' => $metadata1->getId(),
            'control' => $metadata1->getControl(),
            'name' => $metadata1->getName(),
            'label' => $metadata1->getLabel(),
            'description' => [],
            'placeholder' => [],
            'baseId' => $metadata1->getBaseId(),
            'parentId' => $metadata1->getParentId(),
            'constraints' => [],
            'shownInBrief' => false,
            'resourceClass' => $metadata1->getResourceClass(),
        ], [
            'id' => $metadata2->getId(),
            'control' => $metadata2->getControl(),
            'name' => $metadata2->getName(),
            'label' => $metadata2->getLabel(),
            'description' => $metadata2->getDescription(),
            'placeholder' => $metadata2->getPlaceholder(),
            'baseId' => $metadata2->getBaseId(),
            'parentId' => $metadata2->getParentId(),
            'constraints' => [],
            'shownInBrief' => false,
            'resourceClass' => $metadata2->getResourceClass(),
        ]], $responseContent);
    }

    public function testFetchingMetadataFailsWhenInvalidResourceClass() {
        $client = self::createAdminClient();
        $client->apiRequest('GET', self::ENDPOINT, [], ['resourceClass' => 'resourceClass']);
        $this->assertStatusCode(400, $client->getResponse());
    }

    public function testCreatingMetadataKind() {
        $this->createLanguage('EN', 'EN', 'Test English');
        $this->createLanguage('PL', 'PL', 'Test Polish');
        $client = self::createAdminClient();
        $metadataArray = [
            'control' => 'text',
            'name' => 'Test metadata',
            'label' => ['EN' => 'User-friendly label', 'PL' => 'Przyjazna użytkownikowi etykieta'],
            'description' => ['EN' => 'test description', 'PL' => 'testowy opis'],
            'placeholder' => ['EN' => 'test placeholder', 'PL' => 'testowa podpowiedź'],
            'resourceClass' => 'books',
        ];
        $client->apiRequest('POST', self::ENDPOINT, $metadataArray);
        $this->assertStatusCode(201, $client->getResponse());
        $response = json_decode($client->getResponse()->getContent());
        /** @var $metadataRepository MetadataRepository */
        $metadataRepository = self::createClient()->getContainer()->get(MetadataRepository::class);
        $metadata = $metadataRepository->findOne($response->id);
        $this->assertEquals($metadataArray['control'], $metadata->getControl());
        $this->assertEquals($metadataArray['name'], $metadata->getName());
        $this->assertEquals($metadataArray['label'], $metadata->getLabel());
        $this->assertEquals($metadataArray['description'], $metadata->getDescription());
        $this->assertEquals($metadataArray['placeholder'], $metadata->getPlaceholder());
        $this->assertEquals($metadataArray['resourceClass'], $metadata->getResourceClass());
    }

    public function testBasicOrdering() {
        $this->createLanguage('TEST', 'TE', 'Test language');
        $metadata1 = $this->createMetadata('Metadata1', ['TEST' => 'First metadata'], [], [], 'text', 'books');
        $metadata2 = $this->createMetadata('Metadata2', ['TEST' => 'Second metadata'], [], [], 'integer', 'books');
        $client = self::createAdminClient();
        $client->apiRequest('GET', self::ENDPOINT, [], ['resourceClass' => 'books']);
        $response = json_decode($client->getResponse()->getContent());
        array_multisort($response); // make sure IDs are ordered
        $this->assertEquals($metadata1->getId(), $response[0]->id);
        $this->assertEquals($metadata2->getId(), $response[1]->id);
        $client = self::createAdminClient();
        $client->apiRequest('PUT', self::ENDPOINT . '?resourceClass=books', [$response[0]->id, $response[1]->id]);
        $this->assertStatusCode(200, $client->getResponse());
        $client = self::createAdminClient();
        $client->apiRequest('GET', self::ENDPOINT, [], ['resourceClass' => 'books']);
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($metadata1->getId(), $response[0]->id);
        $this->assertEquals($metadata2->getId(), $response[1]->id);
    }

    public function testOrderingWithRepeatedIds() {
        $this->createLanguage('TEST', 'TE', 'Test language');
        $metadata1 = $this->createMetadata('Metadata1', ['TEST' => 'First metadata'], [], [], 'text', 'books');
        $metadata2 = $this->createMetadata('Metadata2', ['TEST' => 'Second metadata'], [], [], 'integer', 'books');
        $client = self::createAdminClient();
        $client->apiRequest('PUT', self::ENDPOINT, [$metadata2->getId(), $metadata1->getId(), $metadata2->getId()]);
        $this->assertStatusCode(400, $client->getResponse());
    }

    public function testOrderingInvalidIds() {
        $metadata1 = Metadata::create('text', 'Metadata1', ['TEST' => 'First metadata'], 'books');
        $metadata2 = Metadata::create('integer', 'Metadata2', ['TEST' => 'Second metadata'], 'books');
        $metadata1->updateOrdinalNumber(0);
        $metadata2->updateOrdinalNumber(1);
        $this->persistAndFlush([$metadata1, $metadata2]);
        $client = self::createAdminClient();
        $client->apiRequest('PUT', self::ENDPOINT, [$metadata2->getId() + 1, $metadata1->getId()]);
    }

    public function testEditingMetadataKind() {
        $this->createLanguage('EN', 'EN', 'Test English');
        $this->createLanguage('PL', 'PL', 'Test Polish');
        $metadata = $this->createMetadata(
            'Metadata',
            ['EN' => 'A metadata', 'PL' => '-'],
            ['EN' => 'Placeholder', 'PL' => '-'],
            ['EN' => 'Description', 'PL' => '-'],
            'integer',
            'books'
        );
        $client = self::createAdminClient();
        $update = [
            'label' => [
                'EN' => 'A metadata',
                'PL' => 'Jakaś metadana',
            ],
            'description' => [
                'EN' => 'Test description',
                'PL' => 'Testowy opis',
            ],
            'placeholder' => [
                'EN' => 'Test placeholder',
                'PL' => 'Testowa zaślepka',
            ],
        ];
        $client->apiRequest('PATCH', self::ENDPOINT . '/' . $metadata->getId(), $update);
        self::assertStatusCode(200, $client->getResponse());
        /** @var Metadata $metadata */
        /** @var Metadata $updatedMetadata */
        $updatedMetadata = self::createClient()->getContainer()->get(MetadataRepository::class)->find($metadata->getId());
        self::assertEquals($update['label'], $updatedMetadata->getLabel());
        self::assertEquals($update['description'], $updatedMetadata->getDescription());
        self::assertEquals($update['placeholder'], $updatedMetadata->getPlaceholder());
        self::assertEquals($metadata->getControl(), $updatedMetadata->getControl());
        self::assertEquals($metadata->getName(), $updatedMetadata->getName());
    }

    public function testChangingMetadataKindNameIsImpossible() {
        $this->createLanguage('EN', 'EN', 'Test English');
        $metadata = $this->createMetadata(
            'Metadata',
            ['EN' => 'A metadata'],
            ['EN' => 'Placeholder'],
            ['EN' => 'Description'],
            'integer',
            'books'
        );
        $client = self::createAdminClient();
        $update = [
            'name' => 'Altered',
        ];
        $client->apiRequest('PATCH', self::ENDPOINT . '/' . $metadata->getId(), $update);
        self::assertStatusCode(200, $client->getResponse());
        /** @var $metadata Metadata */
        /** @var $updatedMetadata Metadata */
        $updatedMetadata = self::createClient()->getContainer()->get(MetadataRepository::class)->find($metadata->getId());
        self::assertEquals($metadata->getName(), $updatedMetadata->getName());
    }
}
