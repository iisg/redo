<?php
namespace Repeka\Tests\Integration\FullTextSearch;

use Repeka\Application\Elasticsearch\ESIndexManager;
use Repeka\Tests\IntegrationTestCaseWithoutDroppingDatabase;

class ElasticsearchCreateIndexCommandIntegrationTest extends IntegrationTestCaseWithoutDroppingDatabase {

    /** @var ESIndexManager */
    private $esIndexManager;

    protected function initializeDatabaseBeforeTheFirstTest() {
    }

    /** @before */
    public function init() {
        $this->esIndexManager = $this->container->get(ESIndexManager::class);
    }

    public function testCreatingElasticsearchIndexWhenDoesNotExist() {
        $this->executeCommand('repeka:elasticsearch:delete-index');
        $this->assertFalse($this->esIndexManager->exists());
        $output = $this->executeCommand('repeka:elasticsearch:create-index');
        $this->assertContains('New index has been created.', $output);
        $this->assertTrue($this->esIndexManager->exists());
    }

    public function testCreatingElasticsearchIndexWhenExists() {
        $this->expectException(\Exception::class);
        $this->executeCommand('repeka:elasticsearch:create-index');
        $this->assertTrue($this->esIndexManager->exists());
    }

    public function testRecreatingElasticsearchIndexWhenExists() {
        $this->executeCommand('repeka:elasticsearch:delete-index');
        $this->assertFalse($this->esIndexManager->exists());
        $this->executeCommand('repeka:elasticsearch:create-index');
        $this->assertTrue($this->esIndexManager->exists());
        $output = $this->executeCommand('repeka:elasticsearch:create-index' . ' --delete-if-exists');
        $this->assertContains('Index already exists - deleting it first', $output);
        $this->assertContains('New index has been created.', $output);
        $this->assertTrue($this->esIndexManager->exists());
    }
}
