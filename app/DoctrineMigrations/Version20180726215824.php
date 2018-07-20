<?php declare(strict_types=1);
namespace Repeka\Migrations;

use Repeka\Domain\Constants\SystemMetadata;

/**
 * Add obligatory resource_label metadata to all resource kinds
 */
class Version20180726215824 extends RepekaMigration {
    public function migrate() {
        $resourceKinds = $this->fetchAll("SELECT id, metadata_list, display_strategies FROM resource_kind");
        foreach ($resourceKinds as $resourceKind) {
            $resourceKind['metadata_list'] = json_decode($resourceKind['metadata_list'], true);
            $displayStrategies = json_decode($resourceKind['display_strategies'], true);
            unset($resourceKind['display_strategies']);
            $metadataData = [
                'id' => SystemMetadata::RESOURCE_LABEL,
            ];
            if ($displayStrategies['header'] ?? false) {
                $metadataData['constraints'] = [
                    'displayStrategy' => $displayStrategies['header'] ?? null,
                ];
            }
            array_unshift($resourceKind['metadata_list'], $metadataData);
            $resourceKind['metadata_list'] = json_encode(array_values($resourceKind['metadata_list']));
            $this->addSql('UPDATE resource_kind SET metadata_list = :metadata_list WHERE id = :id', $resourceKind);
        }
        $this->addSql('ALTER TABLE resource_kind DROP display_strategies');
    }
}
