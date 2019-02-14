<?php
namespace Repeka\Plugins\Redo\Tests\Integration\PkImport;

use Repeka\Tests\IntegrationTestCase;

/** @small */
class PkImportDiscoverMetadataCommandIntegrationTest extends IntegrationTestCase {
    public function testImportUnreadableFile() {
        $filePath = addcslashes(__DIR__ . '/BLABLA.xml', '\\');
        $output = $this->executeCommand('redo:pk-import:discover "' . $filePath . '"');
        $this->assertContains('not readable', $output);
    }

    public function testImportFile() {
        $filePath = addcslashes(__DIR__ . '/dumps/sample-resources-export.xml', '\\');
        $output = $this->executeCommand('redo:pk-import:discover "' . $filePath . '"');
        $this->assertContains('190', $output);
        $this->assertContains('111', $output);
    }
}
