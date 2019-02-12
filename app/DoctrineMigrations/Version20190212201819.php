<?php declare(strict_types=1);
namespace Repeka\Migrations;

use Repeka\Domain\Entity\MetadataControl;

/**
 * Display strategies for all metadata.
 */
class Version20190212201819 extends RepekaMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE metadata ADD display_strategy TEXT DEFAULT NULL');
        $resourceKinds = $this->fetchAll("SELECT id, metadata_list FROM resource_kind");
        $metadataList = $this->fetchAll("SELECT id, constraints FROM metadata WHERE control='display-strategy'");
        foreach ($metadataList as $metadata) {
            $constraints = json_decode($metadata['constraints'], true);
            $displayStrategy = $constraints['displayStrategy'] ?? '';
            $this->addSql(
                "UPDATE metadata SET constraints=constraints-'displayStrategy', display_strategy=:display_strategy, control=:control WHERE id=:id",
                ['id' => $metadata['id'], 'display_strategy' => $displayStrategy, 'control' => MetadataControl::TEXT]
            );
        }
        foreach ($resourceKinds as $resourceKind) {
            $resourceKind['metadata_list'] = json_decode($resourceKind['metadata_list'], true);
            foreach ($resourceKind['metadata_list'] as &$metadataOverrides) {
                if (isset($metadataOverrides['constraints']) && $ds = $metadataOverrides['constraints']['displayStrategy'] ?? null) {
                    $metadataOverrides['displayStrategy'] = $ds;
                    unset($metadataOverrides['constraints']['displayStrategy']);
                }
            }
            $resourceKind['metadata_list'] = json_encode(array_values($resourceKind['metadata_list']));
            $this->addSql('UPDATE resource_kind SET metadata_list = :metadata_list WHERE id = :id', $resourceKind);
        }
    }
}
