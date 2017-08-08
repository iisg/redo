<?php
namespace Repeka\Tests\Integration;

use Repeka\Domain\Entity\Language;
use Repeka\Domain\Repository\LanguageRepository;
use Repeka\Domain\UseCase\Language\LanguageCreateCommand;
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
            'name' => 'testing',
        ]);
        $this->assertStatusCode(201, $client->getResponse());
        /** @var $languageRepository LanguageRepository */
        $languageRepository = $this->container->get(LanguageRepository::class);
        $language = $languageRepository->findOne('TEST');
        $this->assertEquals('TEST', $language->getCode());
        $this->assertEquals('TE', $language->getFlag());
        $this->assertEquals('testing', $language->getName());
    }

    public function testDeletingLanguage() {
        /** @var Language $language */
        $language = $this->handleCommand(new LanguageCreateCommand('TEST', 'TE', 'Test'));
        $client = self::createAdminClient();
        $client->apiRequest('DELETE', self::ENDPOINT . '/' . $language->getCode());
        $this->assertStatusCode(204, $client->getResponse());
        /** @var LanguageRepository $languageRepository */
        $languageRepository = $this->container->get(LanguageRepository::class);
        $this->assertFalse($languageRepository->exists('TEST'));
    }
}
