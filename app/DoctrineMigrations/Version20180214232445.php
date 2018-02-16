<?php
namespace Repeka\Migrations;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Repeka\Domain\Entity\ResourceContents;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Submetadata values.
 */
class Version20180214232445 extends AbstractMigration implements ContainerAwareInterface {
    use ContainerAwareTrait;

    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $connection = $this->container->get('doctrine.orm.entity_manager')->getConnection();
        $this->updateResourceContentsStructure($connection);
        $this->updateResourceKindsDisplayStrategies($connection);
    }

    public function down(Schema $schema) {
        throw new \RuntimeException('There is no way back.');
    }

    private function updateResourceContentsStructure(Connection $connection): void {
        $resources = $connection->fetchAll('SELECT id, contents FROM resource');
        foreach ($resources as $resource) {
            $resource['contents'] = json_decode($resource['contents'], true);
            $resource['contents'] = ResourceContents::fromArray($resource['contents']);
            $resource['contents'] = json_encode($resource['contents']);
            $this->addSql('UPDATE resource SET contents = :contents WHERE id = :id', $resource);
        }
    }

    private function updateResourceKindsDisplayStrategies(Connection $connection) {
        $resourceKinds = $connection->fetchAll('SELECT id, display_strategies FROM resource_kind');
        foreach ($resourceKinds as $resourceKind) {
            $resourceKind['display_strategies'] = json_decode($resourceKind['display_strategies'], true);
            $resourceKind['display_strategies'] = array_map(function ($strategy) {
                return preg_replace('#{{\s*(m-?\d+)\s*}}#', '{{allValues $1}}', $strategy);
            }, $resourceKind['display_strategies']);
            $resourceKind['display_strategies'] = json_encode($resourceKind['display_strategies']);
            $this->addSql('UPDATE resource_kind SET display_strategies = :display_strategies WHERE id = :id', $resourceKind);
        }
    }
}
