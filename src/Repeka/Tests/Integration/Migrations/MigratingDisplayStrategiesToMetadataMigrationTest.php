<?php
namespace Repeka\Tests\Integration\Migrations;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Tests\Integration\Traits\FixtureHelpers;

/**
 * @see Version20180726215824
 */
class MigratingDisplayStrategiesToMetadataMigrationTest extends DatabaseMigrationTestCase {
    use FixtureHelpers;

    /** @before */
    public function prepare() {
        $this->loadDumpV10();
        $this->migrate();
    }

    public function testBookKind() {
        $book = $this->getPhpBookResource();
        $labelMetadata = $book->getKind()->getMetadataById(SystemMetadata::RESOURCE_LABEL);
        $this->assertEquals('{{r|m12}}', $labelMetadata->getConstraints()['displayStrategy']);
    }
}
