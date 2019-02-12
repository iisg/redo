<?php
namespace Repeka\Tests\Integration\Migrations;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Tests\Integration\Traits\FixtureHelpers;

/**
 * @see Version20190212201819
 */
class MigratingDisplayStrategiesToFieldInMetadataTableTest extends DatabaseMigrationTestCase {
    use FixtureHelpers;

    /** @before */
    public function prepare() {
        $this->loadDumpV1_1();
        $this->migrate();
    }

    public function testBookKind() {
        $book = $this->getPhpBookResource();
        $labelMetadata = $book->getKind()->getMetadataById(SystemMetadata::RESOURCE_LABEL);
        $this->assertEquals(MetadataControl::TEXT, $labelMetadata->getControl()->getValue());
        $this->assertArrayNotHasKey('displayStrategy', $labelMetadata->getConstraints());
        $this->assertEquals('{{r|m12}}', $labelMetadata->getDisplayStrategy());
    }
}
