<?php
namespace Repeka\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Repeka\Domain\Constants\SystemMetadata;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Add obligatory parent metadata to all resource kinds
 */
class Version20180130084700 extends AbstractMigration implements ContainerAwareInterface {
    use ContainerAwareTrait;

    public function up(Schema $schema) {
        $connection = $this->container->get('doctrine.orm.entity_manager')->getConnection();
        $results = $connection->fetchAll('SELECT id FROM resource_kind');
        $systemMetadataId = SystemMetadata::PARENT;
        foreach ($results as $index => $resourceKind) {
            $resourceKindId = $resourceKind['id'];
            $this->addSql(<<<SQL
INSERT INTO metadata (id, label, description, placeholder, resource_kind_id, base_id, "constraints") 
VALUES (nextval('metadata_id_seq'), '[]'::JSONB, '[]'::JSONB, '[]'::JSONB, $resourceKindId, $systemMetadataId, 
  json_build_object('maxCount',1,'resourceKind','[]'::JSONB))
SQL
            );
        }
    }

    public function down(Schema $schema) {
        $connection = $this->container->get('doctrine.orm.entity_manager')->getConnection();
        $results = $connection->fetchAll('SELECT id FROM resource_kind');
        foreach ($results as $index => $resourceKindId) {
            $this->addSql('DELETE FROM metadata WHERE resource_kind_id = :id', ['id' => $resourceKindId]);
        }
    }
}
