<?php declare(strict_types=1);
namespace Repeka\Migrations;

use Repeka\Domain\Constants\SystemResourceClass;

/**
 * Fix metadataList in ResourceKinds.
 *
 * Version20180523083737 incorrectly removed GROUP_MEMBER metadata from user resource kinds, leaving metadataList as object instead of an
 * array, e.g.:
 * {"0": {"id": -2}, "2": {"id": 4}}
 *
 * This migration ensures that the metadataList is always an array so we can use PostgreSQL json-array operators, for example in the
 * upcoming migration Version20180718095315
 */
class Version20180718095249 extends RepekaMigration {
    public function migrate() {
        $userResourceClass = SystemResourceClass::USER;
        $userResourceKinds = $this->fetchAll("SELECT id, metadata_list FROM resource_kind WHERE resource_class = '$userResourceClass'");
        foreach ($userResourceKinds as $userResourceKind) {
            $userResourceKind['metadata_list'] = json_encode(
                array_values(
                    json_decode($userResourceKind['metadata_list'], true)
                )
            );
            $this->addSql('UPDATE resource_kind SET metadata_list = :metadata_list WHERE id = :id', $userResourceKind);
        }
    }
}
