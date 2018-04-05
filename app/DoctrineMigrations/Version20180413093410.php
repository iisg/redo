<?php declare(strict_types=1);
namespace Repeka\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Ensure that every resource kind has the PARENT metadata.
 */
class Version20180413093410 extends AbstractMigration {
    public function up(Schema $schema) {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'postgresql',
            'Migration can only be executed safely on \'postgresql\'.'
        );
        $this->addSql(
            <<<UPDATE
          UPDATE resource_kind SET metadata_list = metadata_list || '[{"id": -1}]'::jsonb WHERE id NOT IN(
            SELECT id FROM (
              SELECT id, jsonb_array_elements(metadata_list)->>'id' metadataId FROM resource_kind
            ) AS resourceKindsWithParentMetadata WHERE metadataId='-1'
          )
UPDATE
        );
        $this->addSql('ALTER TABLE metadata ALTER copy_to_child_resource SET NOT NULL');
    }

    public function down(Schema $schema) {
    }
}
