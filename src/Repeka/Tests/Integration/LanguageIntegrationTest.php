<?php
namespace Repeka\Tests\Integration;

use Repeka\Domain\Entity\Language;
use Repeka\Domain\Repository\LanguageRepository;
use Repeka\Tests\IntegrationTestCase;

class LanguageIntegrationTest extends IntegrationTestCase {
    const ENDPOINT = '/api/languages';

    public function testCreatingLanguage() {
        $client = self::createAdminClient();
        $client->apiRequest(
            'POST',
            self::ENDPOINT,
            [
                'code' => 'TEST',
                'flag' => 'TE',
                'name' => 'testing',
            ]
        );
        $this->assertStatusCode(201, $client->getResponse());
        /** @var $languageRepository LanguageRepository */
        $languageRepository = $this->container->get(LanguageRepository::class);
        $language = $languageRepository->findOne('TEST');
        $this->assertEquals('TEST', $language->getCode());
        $this->assertEquals('TE', $language->getFlag());
        $this->assertEquals('testing', $language->getName());
    }

    /** @depends testCreatingLanguage */
    public function testFetchingLanguages() {
        $client = self::createAdminClient();
        $client->apiRequest('GET', self::ENDPOINT);
        $this->assertStatusCode(200, $client->getResponse());
        $responseContent = json_decode($client->getResponse()->getContent(), true);
        $languageCodes = array_map(
            function ($language) {
                return $language['code'];
            },
            $responseContent
        );
        $this->assertContains('TEST', $languageCodes);
    }

    /** @depends testCreatingLanguage */
    public function testDeletingLanguage() {
        /** @var Language $language */
        // $language = $this->handleCommandBypassingFirewall(new LanguageCreateCommand('TEST', 'TE', 'Test'));
        $client = self::createAdminClient();
        $client->apiRequest('DELETE', self::ENDPOINT . '/TEST');
        $this->assertStatusCode(204, $client->getResponse());
        /** @var LanguageRepository $languageRepository */
        $languageRepository = $this->container->get(LanguageRepository::class);
        $this->assertFalse($languageRepository->exists('TEST'));
    }
}
