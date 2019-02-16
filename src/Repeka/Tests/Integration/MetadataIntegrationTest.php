<?php
namespace Repeka\Tests\Integration;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;

class MetadataIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;
    const ENDPOINT = '/api/metadata';

    public function setUp() {
        parent::setUp();
        $this->clearDefaultLanguages();
    }

    public function testFetchingMetadataKinds() {
        $this->createLanguage('TEST', 'TE', 'Test language');
        $metadata1 = $this->createMetadata('Metadata1', ['TEST' => 'First'], [], [], 'textarea');
        $metadata2 = $this->createMetadata('Metadata2', ['TEST' => 'Second'], ['TEST' => 'Hello'], ['TEST' => 'World'], 'integer');
        $client = self::createAdminClient();
        $client->apiRequest('GET', self::ENDPOINT, [], ['resourceClasses' => ['books'], 'topLevel' => true]);
        $this->assertStatusCode(200, $client->getResponse());
        $responseContent = $client->getResponse()->getContent();
        $this->assertJsonStringSimilarToArray(
            [
                [
                    'id' => $metadata1->getId(),
                    'control' => $metadata1->getControl()->getValue(),
                    'name' => $metadata1->getName(),
                    'label' => $metadata1->getLabel(),
                    'description' => [],
                    'placeholder' => [],
                    'baseId' => $metadata1->getBaseId(),
                    'parentId' => $metadata1->getParentId(),
                    'constraints' => [],
                    'groupId' => Metadata::DEFAULT_GROUP,
                    'shownInBrief' => false,
                    'copyToChildResource' => false,
                    'resourceClass' => $metadata1->getResourceClass(),
                    'canDetermineAssignees' => false,
                ],
                [
                    'id' => $metadata2->getId(),
                    'control' => $metadata2->getControl()->getValue(),
                    'name' => $metadata2->getName(),
                    'label' => $metadata2->getLabel(),
                    'description' => $metadata2->getDescription(),
                    'placeholder' => $metadata2->getPlaceholder(),
                    'baseId' => $metadata2->getBaseId(),
                    'parentId' => $metadata2->getParentId(),
                    'constraints' => [],
                    'groupId' => Metadata::DEFAULT_GROUP,
                    'shownInBrief' => false,
                    'copyToChildResource' => false,
                    'resourceClass' => $metadata2->getResourceClass(),
                    'canDetermineAssignees' => false,
                ],
            ],
            $responseContent
        );
    }

    public function testFetchingMetadataWithInvalidResourceClassFails() {
        $client = self::createAdminClient();
        $client->apiRequest('GET', self::ENDPOINT, [], ['resourceClasses' => ['invalidResourceClass']]);
        $this->assertStatusCode(400, $client->getResponse());
    }

    public function testCreatingMetadataKind() {
        $this->createLanguage('EN', 'EN', 'Test English');
        $this->createLanguage('PL', 'PL', 'Test Polish');
        $client = self::createAdminClient();
        $metadataArray = [
            'control' => 'textarea',
            'name' => 'Test metadata',
            'label' => ['EN' => 'User-friendly label', 'PL' => 'Przyjazna użytkownikowi etykieta'],
            'description' => ['EN' => 'test description', 'PL' => 'testowy opis'],
            'placeholder' => ['EN' => 'test placeholder', 'PL' => 'testowa podpowiedź'],
            'resourceClass' => 'books',
            'constraints' => [],
            'groupId' => 'basic',
        ];
        $client->apiRequest('POST', self::ENDPOINT, $metadataArray);
        $this->assertStatusCode(201, $client->getResponse());
        $response = json_decode($client->getResponse()->getContent());
        /** @var $metadataRepository MetadataRepository */
        $metadataRepository = self::createClient()->getContainer()->get(MetadataRepository::class);
        $metadata = $metadataRepository->findOne($response->id);
        $this->assertEquals($metadataArray['control'], $metadata->getControl()->getValue());
        $this->assertEquals(Metadata::normalizeMetadataName($metadataArray['name']), $metadata->getName());
        $this->assertEquals($metadataArray['label'], $metadata->getLabel());
        $this->assertEquals($metadataArray['description'], $metadata->getDescription());
        $this->assertEquals($metadataArray['placeholder'], $metadata->getPlaceholder());
        $this->assertEquals($metadataArray['groupId'], $metadata->getGroupId());
        $this->assertEquals($metadataArray['resourceClass'], $metadata->getResourceClass());
    }

    public function testCreatingSubmetadataKind() {
        $this->createLanguage('TEST', 'TE', 'Test language');
        $parent = $this->createMetadata('Metadata1', ['TEST' => 'First'], [], [], 'textarea');
        $client = self::createAdminClient();
        $metadataArray = [
            'control' => 'textarea',
            'name' => 'Test metadata',
            'label' => ['TEST' => 'User-friendly label'],
            'description' => ['TEST' => 'test description'],
            'placeholder' => ['TEST' => 'test placeholder'],
            'constraints' => [],
            'groupId' => 'basic',
        ];
        $client->apiRequest('POST', $this->oneEntityEndpoint($parent) . '/metadata', ['newChildMetadata' => $metadataArray]);
        $this->assertStatusCode(201, $client->getResponse());
        $response = json_decode($client->getResponse()->getContent());
        /** @var $metadataRepository MetadataRepository */
        $metadataRepository = self::createClient()->getContainer()->get(MetadataRepository::class);
        $metadata = $metadataRepository->findOne($response->id);
        $this->assertEquals($metadataArray['control'], $metadata->getControl()->getValue());
        $this->assertEquals(Metadata::normalizeMetadataName($metadataArray['name']), $metadata->getName());
        $this->assertEquals($metadataArray['label'], $metadata->getLabel());
        $this->assertEquals($metadataArray['description'], $metadata->getDescription());
        $this->assertEquals($metadataArray['placeholder'], $metadata->getPlaceholder());
        $this->assertEquals($metadataArray['groupId'], $metadata->getGroupId());
        $this->assertEquals($parent->getResourceClass(), $metadata->getResourceClass());
        $this->assertEquals($parent->getId(), $metadata->getParentId());
    }

    public function testBasicOrdering() {
        $this->createLanguage('TEST', 'TE', 'Test language');
        $metadata1 = $this->createMetadata('Metadata1', ['TEST' => 'First metadata'], [], [], 'textarea');
        $metadata2 = $this->createMetadata('Metadata2', ['TEST' => 'Second metadata'], [], [], 'integer');
        $client = self::createAdminClient();
        $client->apiRequest('GET', self::ENDPOINT, [], ['resourceClasses' => ['books'], 'topLevel' => true]);
        $response = json_decode($client->getResponse()->getContent());
        array_multisort($response); // make sure IDs are ordered
        $this->assertEquals($metadata1->getId(), $response[0]->id);
        $this->assertEquals($metadata2->getId(), $response[1]->id);
        $client = self::createAdminClient();
        $client->apiRequest('PUT', self::ENDPOINT . '?resourceClass=books', [$response[0]->id, $response[1]->id]);
        $this->assertStatusCode(200, $client->getResponse());
        $client = self::createAdminClient();
        $client->apiRequest('GET', self::ENDPOINT, [], ['resourceClasses' => ['books']]);
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($metadata1->getId(), $response[0]->id);
        $this->assertEquals($metadata2->getId(), $response[1]->id);
    }

    public function testOrderingWithRepeatedIds() {
        $this->createLanguage('TEST', 'TE', 'Test language');
        $metadata1 = $this->createMetadata('Metadata1', ['TEST' => 'First metadata'], [], [], 'textarea');
        $metadata2 = $this->createMetadata('Metadata2', ['TEST' => 'Second metadata'], [], [], 'integer');
        $client = self::createAdminClient();
        $client->apiRequest('PUT', self::ENDPOINT, [$metadata2->getId(), $metadata1->getId(), $metadata2->getId()]);
        $this->assertStatusCode(400, $client->getResponse());
    }

    public function testOrderingInvalidIds() {
        $metadata1 = Metadata::create('books', MetadataControl::TEXTAREA(), 'Metadata1', ['TEST' => 'First metadata']);
        $metadata2 = Metadata::create('books', MetadataControl::INTEGER(), 'Metadata2', ['TEST' => 'Second metadata']);
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
            'constraints' => [],
            'groupId' => 'basic',
        ];
        $client->apiRequest('PATCH', self::ENDPOINT . '/' . $metadata->getId(), $update);
        self::assertStatusCode(200, $client->getResponse());
        /** @var Metadata $metadata */
        /** @var Metadata $updatedMetadata */
        $updatedMetadata = self::createClient()->getContainer()->get(MetadataRepository::class)->find($metadata->getId());
        self::assertEquals($update['label'], $updatedMetadata->getLabel());
        self::assertEquals($update['description'], $updatedMetadata->getDescription());
        self::assertEquals($update['placeholder'], $updatedMetadata->getPlaceholder());
        self::assertEquals($update['groupId'], $updatedMetadata->getGroupId());
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
            'integer'
        );
        $client = self::createAdminClient();
        $update = [
            'name' => 'Altered',
            'constraints' => [],
        ];
        $client->apiRequest('PATCH', self::ENDPOINT . '/' . $metadata->getId(), $update);
        self::assertStatusCode(200, $client->getResponse());
        /** @var $metadata Metadata */
        /** @var $updatedMetadata Metadata */
        $updatedMetadata = self::createClient()->getContainer()->get(MetadataRepository::class)->find($metadata->getId());
        self::assertEquals($metadata->getName(), $updatedMetadata->getName());
    }

    public function testCreatingWithCountConstraints() {
        $this->createLanguage('EN', 'EN', 'Test English');
        $this->createLanguage('PL', 'PL', 'Test Polish');
        $client = self::createAdminClient();
        $metadataArray = [
            'control' => 'textarea',
            'name' => 'Test metadata',
            'label' => ['EN' => 'User-friendly label', 'PL' => 'Przyjazna użytkownikowi etykieta'],
            'description' => ['EN' => 'test description', 'PL' => 'testowy opis'],
            'placeholder' => ['EN' => 'test placeholder', 'PL' => 'testowa podpowiedź'],
            'resourceClass' => 'books',
            'constraints' => ['maxCount' => 1],
        ];
        $client->apiRequest('POST', self::ENDPOINT, $metadataArray);
        $this->assertStatusCode(201, $client->getResponse());
        $response = json_decode($client->getResponse()->getContent());
        /** @var $metadataRepository MetadataRepository */
        $metadataRepository = self::createClient()->getContainer()->get(MetadataRepository::class);
        $metadata = $metadataRepository->findOne($response->id);
        $this->assertEquals($metadataArray['control'], $metadata->getControl());
        $this->assertEquals(Metadata::normalizeMetadataName($metadataArray['name']), $metadata->getName());
        $this->assertEquals($metadataArray['label'], $metadata->getLabel());
        $this->assertEquals($metadataArray['description'], $metadata->getDescription());
        $this->assertEquals($metadataArray['placeholder'], $metadata->getPlaceholder());
    }

    public function testDeletingMetadataThatIsInUse() {
        $this->createLanguage('EN', 'EN', 'Test English');
        $this->createLanguage('PL', 'PL', 'Test Polish');
        $metadata = $this->createMetadata('test', ['PL' => 'test', 'EN' => 'test placeholder']);
        $this->createResourceKind(['PL' => 'testRK', 'EN' => 'testRK'], [$metadata]);
        $client = self::createAdminClient();
        $client->apiRequest('DELETE', self::ENDPOINT . '/' . $metadata->getId());
        $this->assertStatusCode(400, $client->getResponse());
    }

    public function testCreatingDisplayStrategyMetadata() {
        $this->createLanguage('TEST', 'TE', 'Test language');
        $client = self::createAdminClient();
        $metadataArray = [
            'control' => MetadataControl::DISPLAY_STRATEGY,
            'name' => 'Test metadata',
            'label' => ['TEST' => 'User-friendly label'],
            'description' => ['TEST' => 'test description'],
            'placeholder' => ['TEST' => 'test placeholder'],
            'resourceClass' => 'books',
            'constraints' => ['displayStrategy' => 'Unicorn {{r | mTitle }}'],
        ];
        $client->apiRequest('POST', self::ENDPOINT, $metadataArray);
        $this->assertStatusCode(201, $client->getResponse());
        $response = json_decode($client->getResponse()->getContent());
        /** @var $metadataRepository MetadataRepository */
        $metadataRepository = self::createClient()->getContainer()->get(MetadataRepository::class);
        $metadata = $metadataRepository->findOne($response->id);
        $this->assertEquals($metadataArray['control'], $metadata->getControl()->getValue());
        $this->assertEquals(Metadata::normalizeMetadataName($metadataArray['name']), $metadata->getName());
        $this->assertEquals($metadataArray['label'], $metadata->getLabel());
        $this->assertEquals($metadataArray['description'], $metadata->getDescription());
        $this->assertEquals($metadataArray['placeholder'], $metadata->getPlaceholder());
        $this->assertEquals($metadataArray['resourceClass'], $metadata->getResourceClass());
        $this->assertEquals($metadataArray['constraints'], $metadata->getConstraints());
    }

    public function testMetadataNameMustBeUniqueInWholeSystem() {
        $duplicatedName = 'not_unique';
        $this->createLanguage('EN', 'EN', 'Test English');
        $this->createMetadata($duplicatedName, ['EN' => 'Duplicate book'], [], [], MetadataControl::TEXT, 'books');
        $client = self::createAdminClient();
        $newBooksMetadata = [
            'control' => 'text',
            'name' => $duplicatedName,
            'label' => ['EN' => 'Some nice label'],
            'description' => ['EN' => 'Some nice description'],
            'placeholder' => [],
            'resourceClass' => 'books',
            'constraints' => [],
            'groupId' => 'basic',
        ];
        $newDictionaryMetadata = [
            'control' => 'text',
            'name' => $duplicatedName,
            'label' => ['EN' => 'Some nice label'],
            'description' => ['EN' => 'Some nice description'],
            'placeholder' => [],
            'resourceClass' => 'dictionaries',
            'constraints' => [],
            'groupId' => 'basic',
        ];
        $client->apiRequest('POST', self::ENDPOINT, $newBooksMetadata);
        $this->assertStatusCode(400, $client->getResponse());
        $client->apiRequest('POST', self::ENDPOINT, $newDictionaryMetadata);
        $this->assertStatusCode(400, $client->getResponse());
    }

    public function testMetadataNameMustBeUniqueAfterNormalization() {
        $duplicatedName = 'not_unique';
        $this->createLanguage('EN', 'EN', 'Test English');
        $this->createMetadata($duplicatedName, ['EN' => 'Duplicate book'], [], [], MetadataControl::TEXT, 'users');
        $client = self::createAdminClient();
        $newMetadata = [
            'control' => 'text',
            'name' => 'notUnique',
            'label' => ['EN' => 'Some nice label'],
            'description' => ['EN' => 'Some nice description'],
            'placeholder' => [],
            'resourceClass' => 'books',
            'constraints' => [],
            'groupId' => 'basic',
        ];
        $client->apiRequest('POST', self::ENDPOINT, $newMetadata);
        $this->assertStatusCode(400, $client->getResponse());
    }
}
