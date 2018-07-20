<?php
namespace Repeka\Tests\Integration\Migrations;

use Repeka\Tests\Integration\Traits\FixtureHelpers;

class MigratingDisplayStrategiesToTwigMigrationsTest extends DatabaseMigrationTestCase {
    use FixtureHelpers;

    /** @before */
    public function prepare() {
        $this->loadDumpV7();
        $this->migrate();
    }

    public function testBookKind() {
        $this->markTestSkipped('Test is no longer valid as resource kind\'s display strategies have been moved to dynamic metadata.');
        $book = $this->getPhpBookResource();
        $displayStrategies = $book->getKind()->getDisplayStrategies();
        $this->assertEquals('{{r|m(1)}}', $displayStrategies['header']);
        $this->assertEquals('{{r|m(1)}} (ID: {{r.id}})', $displayStrategies['dropdown']);
    }
}
