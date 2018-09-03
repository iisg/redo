<?php
namespace Repeka\Tests\Integration\Migrations;

use Repeka\Tests\Integration\Traits\FixtureHelpers;

class ResourcesAreNotDestroyedByMigrationsTest extends DatabaseMigrationTestCase {
    use FixtureHelpers;

    /** @before */
    public function prepare() {
        $this->loadDumpV5();
        $this->migrate();
    }

    public function testPhpBook() {
        $book = $this->getPhpBookResource();
        $contents = $book->getContents();
        $this->assertEquals(['PHP - to można leczyć!'], $contents->getValues($this->findMetadataByName('Tytuł')));
        $this->assertEquals(
            ['Poradnik dla cierpiących na zwyrodnienie interpretera.'],
            $contents->getValues($this->findMetadataByName('Opis'))
        );
        $this->assertEquals([1337], $contents->getValuesWithoutSubmetadata($this->findMetadataByName('Liczba stron')));
    }
}
