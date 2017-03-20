<?php
namespace Repeka\Tests\Integration;

use Repeka\Domain\Repository\LanguageRepository;
use Repeka\Tests\IntegrationTestCase;

class LanguageIntegrationTest extends IntegrationTestCase {
    const ENDPOINT = '/api/languages';

    public function testFetchingLanguages() {
        $language1 = $this->createLanguage('TEST', 'TE', 'testing');
        $client = self::createAdminClient();
        $client->apiRequest('GET', self::ENDPOINT);
        $this->assertStatusCode(200, $client->getResponse());
        $responseContent = json_decode($client->getResponse()->getContent(), true);
        $languageCodes = array_map(function ($language) {
            return $language['code'];
        }, $responseContent);
        $this->assertContains($language1->getCode(), $languageCodes);
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
        $language = $languageRepository->findOne('TEST');
        $this->assertEquals('TEST', $language->getCode());
        $this->assertEquals('TE', $language->getFlag());
        $this->assertEquals('testing', $language->getName());
    }
}
