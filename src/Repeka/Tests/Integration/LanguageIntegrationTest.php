<?php
namespace Repeka\Tests\Integration;

use Repeka\Domain\Repository\LanguageRepository;
use Repeka\Tests\IntegrationTestCase;

class LanguageIntegrationTest extends IntegrationTestCase {
    const ENDPOINT = '/api/languages';

    public function testFetchingLanguages() {
        $language1 = $this->createLanguage('TEST', 'TE', 'testing');
        $language2 = $this->createLanguage('SCND', 'ND', 'second');
        $client = self::createAdminClient();
        $client->apiRequest('GET', self::ENDPOINT);
        $this->assertStatusCode(200, $client->getResponse());
        $responseContent = $client->getResponse()->getContent();
        $this->assertJsonStringEqualsJsonString(json_encode([[
            'code' => $language1->getCode(),
            'flag' => $language1->getFlag(),
            'name' => $language1->getName()
        ], [
            'code' => $language2->getCode(),
            'flag' => $language2->getFlag(),
            'name' => $language2->getName()
        ]]), $responseContent);
    }

    public function testCreatingLanguage() {
        $client = self::createAdminClient();
        $client->apiRequest('POST', self::ENDPOINT, [
            'code' => 'TEST',
            'flag' => 'TE',
            'name' => 'testing'
        ]);
        $this->assertEquals(201, $client->getResponse()->getStatusCode());
        /** @var $languageRepository LanguageRepository */
        $languageRepository = $this->container->get('repository.language');
        $languages = $languageRepository->findAll();
        $this->assertCount(1, $languages);
        $this->assertEquals('TEST', $languages[0]->getCode());
        $this->assertEquals('TE', $languages[0]->getFlag());
        $this->assertEquals('testing', $languages[0]->getName());
    }
}
