<?php declare(strict_types = 1);

namespace Repeka\Migrations;

use Repeka\Domain\Constants\SystemMetadata;

/**
 * Add obligatory visibility metadata to all resource kinds
 */
class Version20190104102911 extends RepekaMigration {

    public function migrate() {
        $visibilityMetadataId = SystemMetadata::VISIBILITY;
        $addVisibilityMetadataQuery = <<<UPDATE
          UPDATE resource_kind SET metadata_list = metadata_list || '[{"id": $visibilityMetadataId}]'::jsonb WHERE id NOT IN(
            SELECT id FROM (
              SELECT id, jsonb_array_elements(metadata_list)->>'id' metadataId FROM resource_kind
            ) AS resourceKindsWithVisibilityMetadata WHERE metadataId='$visibilityMetadataId'
          )
UPDATE;
        $this->addSql($addVisibilityMetadataQuery);
    }
}
