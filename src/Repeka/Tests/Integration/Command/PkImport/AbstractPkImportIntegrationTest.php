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
        $filePath = addcslashes(__DIR__ . "/dumps/$file.xml", '\\');
        $this->executeCommand(
            "repeka:pk-import:import \"$filePath\" --resourceKindId {$resourceKind->getId()} --no-report --exportFormat PkResourcesDump"
        );
    }

    protected function mapRelations(string $params = '') {
        $this->executeCommand("repeka:pk-import:map-relations $params");
    }
}
