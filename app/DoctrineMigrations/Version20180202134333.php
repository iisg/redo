<?php
namespace Repeka\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Repeka\Domain\Constants\SystemMetadata;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Fill relationship metadata with values if were empty
 */
class Version20180202134333 extends AbstractMigration implements ContainerAwareInterface {
    use ContainerAwareTrait;

    public function up(Schema $schema) {
        $connection = $this->container->get('doctrine.orm.entity_manager')->getConnection();
        $result = $connection->fetchAll('SELECT id FROM resource_kind');
        $resourceKindIdsList = array_column($result, 'id');
        $resourceKindIdsJsonList = '\'' . json_encode($resourceKindIdsList) . '\'';
        $parentMetadataId = SystemMetadata::PARENT;

        $this->addSql(<<<SQL
UPDATE metadata
SET constraints = constraints || CASE
  WHEN constraints->'resourceKind' = '[]'::JSONB THEN jsonb_build_object('resourceKind', $resourceKindIdsJsonList::JSONB)
  ELSE constraints
END
WHERE JSONB_EXISTS(constraints, 'resourceKind') = TRUE AND (base_id IS NULL OR base_id != $parentMetadataId)
SQL
        );
    }

    public function down(Schema $schema) {
    }
}
