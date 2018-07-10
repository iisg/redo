<?php
namespace Repeka\Tests\Integration\Command\PkImport;

use Repeka\Tests\IntegrationTestCase;

class PkImportDiscoverMetadataCommandIntegrationTest extends IntegrationTestCase {
    public function testImportUnreadableFile() {
        $filePath = addcslashes(__DIR__ . '/BLABLA.xml', '\\');
        $output = $this->executeCommand('repeka:pk-import:discover "' . $filePath . '"');
        $this->assertContains('not readable', $output);
    }

    public function testImportFile() {
        $filePath = addcslashes(__DIR__ . '/sample-resources-export.xml', '\\');
        $output = $this->executeCommand('repeka:pk-import:discover "' . $filePath . '"');
        $this->assertContains('190', $output);
        $this->assertContains('111', $output);
    }
}
