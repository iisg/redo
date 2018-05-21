<?php declare(strict_types=1);
namespace Repeka\Migrations;

/**
 * Ensure that every resource kind has the PARENT metadata.
 */
class Version20180413093410 extends RepekaMigration {
    public function migrate() {
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
}
