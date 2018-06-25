<?php declare(strict_types = 1);
namespace Repeka\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add obligatory reproductor metadata to all resource kinds
 */
class Version20180718095315 extends AbstractMigration {
    public function up(Schema $schema) {
        $this->addSql(
            <<<UPDATE
          UPDATE resource_kind SET metadata_list = metadata_list || '[{"id": -4}]'::jsonb WHERE id NOT IN(
            SELECT id FROM (
              SELECT id, jsonb_array_elements(metadata_list)->>'id' metadataId FROM resource_kind
            ) AS resourceKindsWithReproductorMetadata WHERE metadataId='-4'
          )
UPDATE
        );
    }

    public function down(Schema $schema) {
        $this->addSql(
            <<<UPDATE
          UPDATE resource_kind SET metadata_list = metadata_list - '[{"id": -4}]'::jsonb WHERE id IN(
            SELECT id FROM (
              SELECT id, jsonb_array_elements(metadata_list)->>'id' metadataId FROM resource_kind
            ) AS resourceKindsWithReproductorMetadata WHERE metadataId='-4'
          )
UPDATE
        );
    }
}
