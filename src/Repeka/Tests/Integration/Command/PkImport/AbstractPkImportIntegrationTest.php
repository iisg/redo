<?php
namespace Repeka\Tests\Integration\Command\PkImport;

use Repeka\Application\Command\PkImport\PkImportMapRelationsCommand;
use Repeka\Application\Command\PkImport\PkImportResourcesCommand;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;

abstract class AbstractPkImportIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;

    /** @before */
    public function clearImportHistory() {
        @unlink(PkImportResourcesCommand::ID_MAPPING_FILE);
        @unlink(PkImportMapRelationsCommand::MAPPED_RESOURCES_FILE);
    }

    protected function createSimpleMetadata(string $name, MetadataControl $control, string $resourceClass): Metadata {
        return $this->createMetadata($name, ['PL' => $name, 'EN' => $name], [], [], $control, $resourceClass);
    }

    protected function importFile(string $file, ResourceKind $resourceKind) {
        $filePath = addcslashes(__DIR__ . "/$file.xml", '\\');
        $configPath = addcslashes(__DIR__ . "/$file.json", '\\');
        $this->executeCommand("repeka:pk-import:import \"$filePath\" \"$configPath\" {$resourceKind->getId()}");
        $this->executeCommand("repeka:pk-import:map-relations");
    }
}
