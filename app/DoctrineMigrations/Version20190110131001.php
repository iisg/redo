<?php declare(strict_types = 1);
namespace Repeka\Migrations;

use Repeka\Domain\Constants\SystemMetadata;

/**
 * Add obligatory visibility-in-relationship metadata to all resource kinds
 */
class Version20190110131001 extends RepekaMigration {

    public function migrate() {
        $teaserVisibilityMetadataId = SystemMetadata::TEASER_VISIBILITY;
        $addTeaserVisibilityMetadataQuery = <<<UPDATE
          UPDATE resource_kind SET metadata_list = metadata_list || '[{"id": $teaserVisibilityMetadataId}]'::jsonb WHERE id NOT IN(
            SELECT id FROM (
              SELECT id, jsonb_array_elements(metadata_list)->>'id' metadataId FROM resource_kind
            ) AS resourceKindsWithVisibilityMetadata WHERE metadataId='$teaserVisibilityMetadataId'
          )
UPDATE;
        $this->addSql($addTeaserVisibilityMetadataQuery);
    }
}
