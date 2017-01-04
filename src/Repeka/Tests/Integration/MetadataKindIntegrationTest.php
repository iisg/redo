<?php
namespace Repeka\Tests\Integration;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Tests\IntegrationTestCase;

class MetadataKindIntegrationTest extends IntegrationTestCase {
    const ENDPOINT = '/api/metadata';

    public function testFetchingMetadataKinds() {
        $this->createLanguage('TEST', 'TE', 'Test language');
        $metadata1 = $this->createMetadata('Metadata1', ['TEST' => 'First'], [], [], 'text');
        $metadata2 = $this->createMetadata('Metadata2', ['TEST' => 'Second'], ['TEST' => 'Hello'], ['TEST' => 'World'], 'integer');
        $client = self::createAdminClient();
        $client->apiRequest('GET', self::ENDPOINT);
        $this->assertStatusCode(200, $client->getResponse());
        $responseContent = $client->getResponse()->getContent();
        $this->assertJsonStringSimilarToArray([[
            'id' => $metadata1->getId(),
            'control' => $metadata1->getControl(),
            'name' => $metadata1->getName(),
            'label' => $metadata1->getLabel(),
            'description' => [],
            'placeholder' => [],
            'baseId' => $metadata1->getBaseId()
        ], [
            'id' => $metadata2->getId(),
            'control' => $metadata2->getControl(),
            'name' => $metadata2->getName(),
            'label' => $metadata2->getLabel(),
            'description' => $metadata2->getDescription(),
            'placeholder' => $metadata2->getPlaceholder(),
            'baseId' => $metadata2->getBaseId()
        ]], $responseContent);
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
            'placeholder' => ['EN' => 'test placeholder', 'PL' => 'testowa podpowiedź']
        ];
        $client->apiRequest('POST', self::ENDPOINT, $metadataArray);
        $this->assertStatusCode(201, $client->getResponse());
        /** @var $metadataRepository MetadataRepository */
        $metadataRepository = self::createClient()->getContainer()->get('repository.metadata');
        $metadata = $metadataRepository->findAll();
        $this->assertCount(1, $metadata);
        $this->assertEquals($metadataArray['control'], $metadata[0]->getControl());
        $this->assertEquals($metadataArray['name'], $metadata[0]->getName());
        $this->assertEquals($metadataArray['label'], $metadata[0]->getLabel());
        $this->assertEquals($metadataArray['description'], $metadata[0]->getDescription());
        $this->assertEquals($metadataArray['placeholder'], $metadata[0]->getPlaceholder());
    }

    public function testBasicOrdering() {
        $this->createLanguage('TEST', 'TE', 'Test language');
        $metadata1 = $this->createMetadata('Metadata1', ['TEST' => 'First metadata'], [], [], 'text');
        $metadata2 = $this->createMetadata('Metadata2', ['TEST' => 'Second metadata'], [], [], 'integer');
        $client = self::createAdminClient();
        $client->apiRequest('GET', self::ENDPOINT);
        $response = json_decode($client->getResponse()->getContent());
        array_multisort($response); // make sure IDs are ordered
        $this->assertEquals($metadata1->getId(), $response[0]->id);
        $this->assertEquals($metadata2->getId(), $response[1]->id);
        $client = self::createAdminClient();
        $client->apiRequest('PUT', self::ENDPOINT, [$metadata2->getId(), $metadata1->getId()]);
        $this->assertStatusCode(200, $client->getResponse());
        $client = self::createAdminClient();
        $client->apiRequest('GET', self::ENDPOINT);
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($metadata2->getId(), $response[0]->id);
        $this->assertEquals($metadata1->getId(), $response[1]->id);
    }

    public function testOrderingInvalidIds() {
        $metadata1 = Metadata::create('text', 'Metadata1', ['TEST' => 'First metadata']);
        $metadata2 = Metadata::create('integer', 'Metadata2', ['TEST' => 'Second metadata']);
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
            'integer'
        );
        $client = self::createAdminClient();
        $update = [
            'label' => [
                'EN' => 'A metadata',
                'PL' => 'Jakaś metadana'
            ],
            'description' => [
                'EN' => 'Test description',
                'PL' => 'Testowy opis'
            ],
            'placeholder' => [
                'EN' => 'Test placeholder',
                'PL' => 'Testowa zaślepka'
            ]
        ];
        $client->apiRequest('PATCH', '/api/metadata/' . $metadata->getId(), $update);
        self::assertStatusCode(200, $client->getResponse());
        /** @var $metadata Metadata */
        $updatedMetadata = self::createClient()->getContainer()->get('repository.metadata')->find($metadata->getId());
        self::assertEquals($update['label'], $updatedMetadata->getLabel());
        self::assertEquals($update['description'], $updatedMetadata->getDescription());
        self::assertEquals($update['placeholder'], $updatedMetadata->getPlaceholder());
        self::assertEquals($metadata->getControl(), $updatedMetadata->getControl());
        self::assertEquals($metadata->getName(), $updatedMetadata->getName());
    }

    public function testChangingMetadataKindNameIsImpossible() {
        $this->createLanguage('EN', 'EN', 'Test English');
        $metadata = $this->createMetadata('Metadata', ['EN' => 'A metadata'], ['EN' => 'Placeholder'], ['EN' => 'Description'], 'integer');
        $client = self::createAdminClient();
        $update = [
            'name' => 'Altered'
        ];
        $client->apiRequest('PATCH', '/api/metadata/' . $metadata->getId(), $update);
        self::assertStatusCode(200, $client->getResponse());
        /** @var $metadata Metadata */
        $updatedMetadata = self::createClient()->getContainer()->get('repository.metadata')->find($metadata->getId());
        self::assertEquals($metadata->getName(), $updatedMetadata->getName());
    }
}
