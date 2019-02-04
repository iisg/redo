<?php
namespace Repeka\Tests\Integration\Controller\Site;

use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;

/** @small */
class ResourcesExposureControllerIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;

    protected function initializeDatabaseForTests() {
        $this->loadAllFixtures();
    }

    public function testFetchingResourceTitleFromExposedEndpoint() {
        $client = self::createClient();
        $phpBook = $this->getPhpBookResource();
        $client->request('GET', '/resources-title/' . $phpBook->getId());
        $this->assertStatusCode(200, $client->getResponse());
        $responseContent = $client->getResponse()->getContent();
        $this->assertEquals($phpBook->getValues($this->findMetadataByName('Tytuł'))[0], $responseContent);
    }

    public function testFetchingResourceTitleFromResourceThatDoesNotContainItResultsIn404() {
        $client = self::createClient();
        $client->request('GET', '/resources-title/' . $this->getAdminUser()->getUserData()->getId());
        $this->assertStatusCode(404, $client->getResponse());
    }

    public function testFetchingResourceTitleFromResourceThatDoesNotExistResultsIn404() {
        $client = self::createClient();
        $client->request('GET', '/resources-title/8364637');
        $this->assertStatusCode(404, $client->getResponse());
    }

    public function testFetchingAboutPage() {
        $client = self::createClient();
        $client->request('GET', '/about');
        $this->assertStatusCode(200, $client->getResponse());
        $responseContent = $client->getResponse()->getContent();
        $this->assertContains('<repeka-version>', $responseContent);
    }
}
