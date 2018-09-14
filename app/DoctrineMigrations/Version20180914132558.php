<?php declare(strict_types=1);
namespace Repeka\Migrations;

use Repeka\Domain\Constants\SystemMetadata;

/**
 * Delete invalid overrides of the reproductor metadata.
 */
class Version20180914132558 extends RepekaMigration {
    public function migrate() {
        $resourceKinds = $this->fetchAll("SELECT id, metadata_list FROM resource_kind");
        foreach ($resourceKinds as $resourceKind) {
            $resourceKind['metadata_list'] = json_decode($resourceKind['metadata_list'], true);
            $resourceKind['metadata_list'] = array_filter(
                $resourceKind['metadata_list'],
                function (array $metadataSpec) {
                    return $metadataSpec['id'] != SystemMetadata::REPRODUCTOR;
                }
            );
            $resourceKind['metadata_list'] = json_encode(array_values($resourceKind['metadata_list']));
            $this->addSql('UPDATE resource_kind SET metadata_list = :metadata_list WHERE id = :id', $resourceKind);
        }
        $this->addSql(Version20180718095315::ADD_REPRODUCTOR_METADATA_QUERY);
    }
}
