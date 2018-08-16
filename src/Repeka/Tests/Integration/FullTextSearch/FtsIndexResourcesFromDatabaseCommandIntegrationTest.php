<?php
namespace Repeka\Tests\Integration\FullTextSearch;

use Repeka\Application\Elasticsearch\ESIndexManager;
use Repeka\Tests\IntegrationTestCase;

class FtsIndexResourcesFromDatabaseCommandIntegrationTest extends IntegrationTestCase {

    /** @var EsIndexManager */
    private $esIndexManager;

    public function setUp() {
        parent::setUp();
        $this->loadAllFixtures();
        $container = self::createClient()->getContainer();
        $this->esIndexManager = $container->get(ESIndexManager::class);
    }

    public function testMappingDatabaseToElasticsearchIndexWhenDoesNotExist() {
        $this->executeCommand('repeka:elasticsearch:delete-index');
        $this->assertFalse($this->esIndexManager->exists());
        $output = $this->executeCommand('repeka:fts:index-database');
        $this->assertContains('Index ' . $this->esIndexManager->getIndex() . 'does not exist', $output);
        $this->assertFalse($this->esIndexManager->exists());
    }

    public function testMappingDatabaseToElasticsearchIndexWhenExists() {
        $this->executeCommand('repeka:elasticsearch:create-index' . ' --delete-if-exists');
        $this->assertTrue($this->esIndexManager->exists());
        $output = $this->executeCommand('repeka:fts:index-database');
        $this->assertContains('All resources from the database have been inserted into the elasticsearch index', $output);
    }
}
