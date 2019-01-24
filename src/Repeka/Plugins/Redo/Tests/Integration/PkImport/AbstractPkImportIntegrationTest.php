<?php
namespace Repeka\Plugins\Redo\Tests\Integration\PkImport;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Plugins\Redo\Command\Import\PkImportMapRelationsCommand;
use Repeka\Plugins\Redo\Command\Import\PkImportResourcesCommand;
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
        $filePath = addcslashes(__DIR__ . "/dumps/$file.xml", '\\');
        $this->executeCommand(
            "redo:pk-import:import \"$filePath\" --resourceKindId {$resourceKind->getId()} --no-report --exportFormat PkResourcesDump"
        );
    }

    protected function mapRelations(string $params = '') {
        $this->executeCommand("redo:pk-import:map-relations $params");
    }
}
