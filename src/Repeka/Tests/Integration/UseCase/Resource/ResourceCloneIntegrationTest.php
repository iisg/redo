<?php
namespace Repeka\Tests\Integration\UseCase\Resource;

use Repeka\Domain\UseCase\Resource\ResourceCloneCommand;
use Repeka\Domain\UseCase\Resource\ResourceCloneManyTimesCommand;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;

class ResourceCloneIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;

    public function initializeDatabaseForTests() {
        $this->loadAllFixtures();
    }

    public function testCloning() {
        $cloneCommand = new ResourceCloneCommand($this->getPhpBookResource());
        $this->handleCommandBypassingFirewall($cloneCommand);
        $query = ResourceListQuery::builder()->filterByContents(['tytul' => 'leczyć'])->build();
        $this->assertCount(2, $this->handleCommandBypassingFirewall($query));
    }

    public function testCloningMultipleTimes() {
        $cloneCommand = new ResourceCloneManyTimesCommand($this->getPhpBookResource(), 10);
        $this->handleCommandBypassingFirewall($cloneCommand);
        $query = ResourceListQuery::builder()->filterByContents(['tytul' => 'leczyć'])->build();
        $this->assertCount(11, $this->handleCommandBypassingFirewall($query));
    }
}
